
(function (){

	/**
	 * @bxjs_lang_path js_mobile.php
	 */
	if (BX.ImMobile)
	return;

BX.ImMobile = function(params)
{
	BX.browser.addGlobalClass();

	if(typeof params.user_tz_offset != "undefined")
	{
		BX.message({"USER_TZ_OFFSET":params.user_tz_offset})
	}
	else
	{
		if(typeof(BX.message("USER_TZ_AUTO")) == 'undefined' || BX.message("USER_TZ_AUTO") == 'Y')
			BX.message({"USER_TZ_OFFSET": -(new Date).getTimezoneOffset()*60-parseInt(BX.message("SERVER_TZ_OFFSET"))});
	}

	if (typeof(BX.MessengerCommon) != 'undefined')
		BX.MessengerCommon.setBxIm(this);

	this.mobileVersion = true;
	this.mobileAction = params.mobileAction? params.mobileAction: 'none';
	this.mobileActionCache = false;
	this.mobileActionRun = false;

	this.linesDetailCounter = {};
	this.dialogDetailCounter = {};

	this.callController = null;

	this.revision = 6; // mobile api revision - check include.php
	this.errorMessage = '';
	this.isAdmin = params.isAdmin || false;
	this.bitrixNetwork = params.bitrixNetwork || false;
	this.bitrixNetwork2 = params.bitrixNetwork2 || false;
	this.bitrixOpenLines = params.bitrixOpenLines || false;
	this.bitrix24 = params.bitrix24 || false;
	this.bitrixIntranet = params.bitrixIntranet || false;
	this.bitrix24net = params.bitrix24net || false;
	this.bitrixXmpp = params.bitrixXmpp || false;
	this.ppStatus = params.ppStatus || false;
	this.ppServerStatus = this.ppStatus? params.ppServerStatus: false;
	this.updateStateInterval = params.updateStateInterval || 90;
	this.desktopStatus = params.desktopStatus || false;
	this.desktopVersion = params.desktopVersion || 0;
	this.xmppStatus = false;
	this.lastRecordId = 0;
	this.userId = params.userId;
	this.userEmail = params.userEmail || '';
	this.userGender = params.userGender || 'M';
	this.path = params.path || {};
	this.language = params.language || 'en';
	this.options = params.options || {};
	this.init = typeof(params.init) != 'undefined'? params.init: true;
	this.tryConnect = true;
	this.animationSupport = true;

	this.keyboardShow = false;

	this.sendAjaxTry = 0;
	this.pathToRoot = BX.message('MobileSiteDir') ? BX.message('MobileSiteDir') : '/';
	this.pathToAjax = this.pathToRoot+'mobile/ajax.php?mobile_action=im&';
	this.pathToCallAjax = this.pathToAjax+'call&';
	this.pathToFileAjax = this.pathToAjax+'upload&';
	this.pathToBlankImage = '/bitrix/js/im/images/blank.gif';
	this.pathToCrmDeal = this.pathToRoot+"mobile/crm/deal/?page=view&deal_id=#ID#";
	this.pathToCrmLead = this.pathToRoot+"mobile/crm/lead/?page=view&lead_id=#ID#";
	this.pathToCrmCompany = this.pathToRoot+"mobile/crm/company/?page=view&company_id=#ID#";
	this.pathToCrmContact = this.pathToRoot+"mobile/crm/contact/?page=view&contact_id=#ID#";

	this.notifyCount = params.notifyCount || 0;
	this.messageCount = params.messageCount || 0;
	this.messageCountArray = {};

	this.settings = params.settings || {};
	this.settingsNotifyBlocked = params.settingsNotifyBlocked || {};

	this.saveSettingsTimeout = [];
	this.timeoutUpdateCounters = null;
	this.timeoutUpdateStateLight = null;

	params.notify = params.notify || {};
	params.message = params.message || {};
	params.recent = params.recent || {};

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

	if (BX.ImNotifyMobile)
	{
		this.notify = new BX.ImNotifyMobile(this, {
			'counters': params.counters || {},
			'notify': params.notify || {},
			'unreadNotify' : params.unreadNotify || {},
			'flashNotify' : params.flashNotify || {},
			'countNotify' : params.countNotify || 0,
			'loadNotify' : params.loadNotify || false
		});
	}
	else
	{
		this.notify = {};
	}

	this.disk = new BX.ImDiskManagerMobile(this, {
		notifyClass: this.notify,
		files: params.files || {},
		enable: params.disk && params.disk.enable,
		enableExternal: params.disk && params.disk.external
	});
	this.notify.disk = this.disk;

	this.messenger = new BX.ImMessengerMobile(this, {
		'openChatEnable': params.openChatEnable || true,
		'updateStateInterval': params.updateStateInterval,
		'notifyClass': this.notify,
		'diskClass': this.disk,
		'recent': params.recent || {},
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
		'bot' : params.bot || {},
		'smile' : params.smile || false,
		'smileSet' : params.smileSet || false,
		'history' : params.history || {}
	});
	this.notify.messenger = this.messenger;
	this.disk.messenger = this.messenger;

	this.webrtc = new BX.ImWebRTCMobile(this, {
		'callMethod': params.mobileCallMethod? params.mobileCallMethod: 'device',
		'desktopClass': this.desktop,
		'phoneEnabled': params.webrtc && params.webrtc.phoneEnabled || false,
		'mobileSupport': params.webrtc && params.webrtc.mobileSupport || false,
		'phoneDeviceActive': params.webrtc && params.webrtc.phoneDeviceActive || 'N',
		'phoneDeviceCall': params.webrtc && params.webrtc.phoneDeviceCall || 'Y',
		'phoneCrm': params.phoneCrm && params.phoneCrm || {},
		'turnServer': params.webrtc && params.webrtc.turnServer || '',
		'turnServerFirefox': params.webrtc && params.webrtc.turnServerFirefox || '',
		'turnServerLogin': params.webrtc && params.webrtc.turnServerLogin || '',
		'turnServerPassword': params.webrtc && params.webrtc.turnServerPassword || ''
	});
	this.messenger.webrtc = this.webrtc;

	this.desktop = {'ready': function(){ return false;}, 'run': function(){ return false;}};
	this.messenger.desktop = this.desktop;

	if (this.init)
	{
		BX.onCustomEvent(window, 'onImMobileInit', [this]);
		app.pullDownLoadingStop();
		this.mobileActionPrepare(params);
	}

	if (this.mobileAction == 'DIALOG')
	{
		BXMobileApp.UI.Page.TopBar.title.setText('');
		BXMobileApp.UI.Page.TopBar.title.setDetailText('');
	}

	this.mobileActionFromCache();
	this.mobileActionReady();

	/*
	BX.addCustomEvent("onFrameDataRequestFail", BX.delegate(function(data){
		this.mobileActionReady();
	}, this));

	BX.addCustomEvent('onFrameDataProcessed', BX.delegate(function(element, fromCache)
	{
		for (var i = 0; i < element.length; i++)
		{
			if (element[i]['ID'].indexOf('im_component_') >= 0)
			{
				this.mobileActionFromCache();
			}
		}
	},this));
	*/
}


BX.ImMobile.prototype.saveSettings = function(settings)
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

BX.ImMobile.prototype.setLocalConfig = function()
{
}

BX.ImMobile.prototype.getLocalConfig = function()
{
}

BX.ImMobile.prototype.playSound = function(sound)
{
	var whiteList = {'ringtone': BX.MobileCallUI.form.sound.INCOMING, 'start': BX.MobileCallUI.form.sound.START_CALL};

	whiteList[BX.MobileCallUI.form.sound.START_CALL] = BX.MobileCallUI.form.sound.START_CALL;
	whiteList[BX.MobileCallUI.form.sound.INCOMING] = BX.MobileCallUI.form.sound.INCOMING;

	if (!whiteList[sound])
		return false;

	BX.MobileCallUI.form.playSound(whiteList[sound])
};

BX.ImMobile.prototype.stopSound = function()
{
	BX.MobileCallUI.form.stopSound();
};

BX.ImMobile.prototype.repeatSound = function(sound, time)
{
	this.playSound(sound);
};

BX.ImMobile.prototype.stopRepeatSound = function(sound, send)
{
	BX.MobileCallUI.form.stopSound();
};



BX.ImMobile.prototype.phoneTo = function(number, params)
{
	params = params? params: {};
	if (typeof(params) != 'object')
	{
		try { params = JSON.parse(params); } catch(e) { params = {} }
	}

	if (!this.webrtc.phoneEnabled)
	{
		params.callMethod = 'device';
	}

	if (this.mobileAction != 'RECENT')
	{
		BX.MobileTools.phoneTo(number, params);
		return true;
	}

	if (!params.callMethod)
	{
		params.callMethod = this.webrtc.callMethod;
	}

	if (params.callMethod == 'telephony')
	{
		this.webrtc.phoneCall(number, params);
	}
	else
	{
		document.location.href = "tel:" + this.correctPhoneNumber(number);
	}
}

BX.ImMobile.prototype.correctPhoneNumber = function(number)
{
	if(!BX.type.isNotEmptyString(number))
		return number;

	if(number.length < 10)
		return number;

	if(number.substr(0, 1) === '+')
		return number;

	if(number.substr(0, 3) === '011')
		return number;

	if(number.substr(0, 2) === '82')
		return '+' + number;
	else if(number.substr(0, 1) === '8')
		return number;

	return '+' + number;
}

BX.ImMobile.prototype.openConfirm = function(params, buttons)
{
	var confirm = {};
	if (typeof(params) != "object")
	{
		confirm = {
			title: '',
			text: params,
			params: {},
			buttons: [],
			actions: []
		}
	}
	else
	{
		confirm.title = params.title || '';
		confirm.text = params.message || '';
		confirm.params = params.params || {};
		confirm.buttons = [];
		confirm.actions = [];
	}

	if (typeof(buttons) == "undefined" || typeof(buttons) == "object" && buttons.length <= 0)
	{
		confirm.buttons = [BX.message('IM_MENU_CANCEL')];
		confirm.actions = [function(){}];
	}
	else
	{
		confirm.buttons = [];
		confirm.actions = [];
		for (var i = 0; i < buttons.length; i++)
		{
			confirm.buttons[i] = buttons[i].text;

			if (typeof(buttons[i].callback) == 'function')
			{
				confirm.actions[i+1] = buttons[i].callback;
			}
			else
			{
				confirm.actions[i+1] = function(){}
			}
		}
	}

	app.confirm({
		title : confirm.title,
		text : confirm.text,
		buttons : confirm.buttons,
		callback : function (btnNum)
		{
			if (typeof(confirm.actions[btnNum]) == 'function')
			{
				confirm.actions[btnNum](confirm.params);
			}
		}
	});
};

BX.ImMobile.prototype.openRecentList = function()
{
	BXMobileApp.UI.Slider.setState(BXMobileApp.UI.Slider.state.CENTER);

	setTimeout(function(){
		BXMobileApp.UI.Slider.setState(BXMobileApp.UI.Slider.state.RIGHT);
	}, 500);
}

BX.ImMobile.prototype.mobileActionPrepare = function(params)
{
	if (this.mobileAction == 'RECENT')
	{

	}
	else if (this.mobileAction == 'INIT')
	{
		this.initPageAction();
	}
	else if (this.mobileAction == 'DIALOG')
	{
		this.dialogPageAction(params);
	}
}

BX.ImMobile.prototype.mobileActionFromCache = function()
{
	if (this.mobileActionCache)
		return false;

	this.mobileActionCache = true;

	BX.addClass(document.body, 'im-page-from-cache');

	if (this.mobileAction == 'RECENT')
	{
		this.messenger.drawRecentList();
	}
	else if (this.mobileAction == 'DIALOG')
	{
		this.messenger.currentTab = 0;
		this.messenger.openChatFlag = false;
		this.messenger.openCallFlag = false;
		this.messenger.openLinesFlag = false;
		this.messenger.showMessage = {}
		this.messenger.unreadMessage = {};
	}
	this.mobileActionReady();
}

BX.ImMobile.prototype.mobileActionReady = function()
{
	if (this.mobileActionRun)
		return false;

	this.mobileActionRun = true;

	BXMobileApp.UI.Page.LoadingScreen.hide();

	BX.removeClass(document.body, 'im-page-from-cache');

	BX.MessengerCommon.pullEvent();

	if (this.mobileAction == 'RECENT')
	{
		this.recentPageAction();

		if (!window.imRecentFastClick)
		{
			window.imRecentFastClick = true;
			BitrixMobile.fastClick.bindDelegate(this.messenger.popupContactListElementsWrap, {className: 'bx-messenger-cl-item'}, BX.delegate(BX.MessengerCommon.contactListClickItem, BX.MessengerCommon));
			BitrixMobile.fastClick.bindDelegate(this.messenger.popupContactListElementsWrap, {className: 'bx-messenger-cl-group-title'}, BX.delegate(BX.MessengerCommon.contactListToggleGroup, BX.MessengerCommon));

			BitrixMobile.fastClick.bindDelegate(this.messenger.popupContactListElementsWrap, {className: 'bx-messenger-chatlist-more-wrap'}, BX.delegate(function(e){
				if (BX.hasClass(BX.proxy_context.parentNode, 'bx-messenger-chatlist-show-all'))
				{
					this.messenger.contactListShowed[BX.proxy_context.firstChild.getAttribute('data-id')] = false;
					BX.proxy_context.firstChild.innerHTML = BX.proxy_context.firstChild.getAttribute('data-text');
					BX.removeClass(BX.proxy_context.parentNode, 'bx-messenger-chatlist-show-all');

					var pos = BX.pos(BX.proxy_context);
					document.body.scrollTop = pos.top-100;
				}
				else
				{
					this.messenger.contactListShowed[BX.proxy_context.firstChild.getAttribute('data-id')] = true;
					BX.proxy_context.firstChild.innerHTML = BX.message('IM_CL_HIDE');
					BX.addClass(BX.proxy_context.parentNode, 'bx-messenger-chatlist-show-all');
					BitrixMobile.LazyLoad.showImages();
				}
			}, this));
			BitrixMobile.fastClick.bindDelegate(this.messenger.popupContactListElementsWrap, {className: 'bx-messenger-chatlist-search-button'}, BX.delegate(function(e){
				this.messenger.realSearch = true;

				this.messenger.popupContactListElementsWrap.appendChild(BX.create("div", {
					props : { className: "bx-messenger-cl-item-search"},
					html : BX.message('IM_M_CL_SEARCH')
				}));
				BX.remove(BX.proxy_context.parentNode);

				BX.MessengerCommon.contactListRealSearch(this.messenger.contactListSearchText);
			}, this));
		}

		BXMobileApp.addCustomEvent("onImDialogOpen", BX.delegate(function (params)
		{
			this.messenger.openMessenger(params.id, false, false);
		}, this));

		BXMobileApp.addCustomEvent("onImDialogClose", BX.delegate(function (params)
		{
			this.messenger.closeMessenger(params.id);
		}, this));

		BX.addCustomEvent("onImDialogNetworkOpen", BX.delegate(function (params)
		{
			for (var i = 0; i < this.messenger.recent.length; i++)
			{
				if (this.messenger.recent[i].userId == params.NETWORK_ID)
				{
					this.messenger.recent[i].userId = params.USER_ID;
					this.messenger.recent[i].recipientId = params.USER_ID;
					this.messenger.recent[i].senderId = params.USER_ID;
				}
			}

			params.USER.last_activity_date = new Date(params.USER.last_activity_date);
			params.USER.mobile_last_date = new Date(params.USER.mobile_last_date);
			params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
			params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

			this.messenger.users[params.USER_ID] = params.USER;

			this.messenger.currentTab = params.USER_ID;
			BX.MessengerCommon.userListRedraw();
		}, this));
	}
	else if (this.mobileAction == 'DIALOG')
	{
		BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", BX.delegate(function (params){
			if (BX.MessengerCommon.isMobile() && this.isBackground())
				return false;

			BX.MessengerCommon.readMessage(this.messenger.currentTab);
		}, this));

		BX.addCustomEvent("onOpenPageAfter", BX.delegate(function(){
			if (BX.MessengerCommon.isMobile() && this.isBackground())
				return false;

			BX.MessengerCommon.readMessage(this.messenger.currentTab);
			this.messenger.dialogStatusRedrawDelay();
			BXMobileApp.onCustomEvent('onImDialogOpen', {id: this.messenger.currentTab}, true);
		}, this));

		BX.addCustomEvent("onHidePageBefore", BX.delegate(function(){
			BXMobileApp.onCustomEvent('onImDialogClose', {id: this.messenger.currentTab}, true);
		}, this));

		BXMobileApp.UI.Page.TextPanel.setUseImageButton(true);
		var panelParams = {
			callback: BX.delegate(function (data)
			{
				if (data.event && data.event == "onKeyPress")
				{
					if (BX.util.trim(data.text).length > 2)
					{
						BX.MessengerCommon.sendWriting(this.messenger.currentTab);
					}
					this.messenger.textareaHistory[this.messenger.currentTab] = data.text;
				}
			}, this),
			smileButton: {},
			useImageButton:true,
			attachFileSettings:
			{
				"resize":
				{
					"quality":40,
					"destinationType":1,
					"sourceType":1,
					"targetWidth":1000,
					"targetHeight":1000,
					"encodingType":0,
					"mediaType":0,
					"allowsEdit":false,
					"correctOrientation":true,
					"saveToPhotoAlbum":true,
					"popoverOptions":false,
					"cameraDirection":0
				},
				"showAttachedFiles": true,
				"sendLocalFileMethod": "base64",
				"maxAttachedFilesCount": 1
			},
			attachButton:{
				items:[
					{
						"id":"disk",
						"name":BX.message("IM_B24DISK_MSGVER_1"),
						"dataSource":
						{
							"multiple":false,
							"url":"/mobile/?mobile_action=disk_folder_list&type=user&path=%2F&entityId="+BX.message("USER_ID"),
							"TABLE_SETTINGS":{
								"searchField":true,
								"showtitle":true,
								"modal":true,
								"name":BX.message("IM_CHOOSE_FILE_TITLE")
							}
						}
					},
					{
						"id":"mediateka",
						"name":BX.message("IM_CHOOSE_PHOTO")
					},
					{
						"id":"camera",
						"name":BX.message("IM_CAMERA_ROLL")
					}
				]
			},
			placeholder: BX.message('IM_M_TEXTAREA'),
			mentionDataSource: {outsection:false, url:this.pathToRoot+"mobile/index.php?mobile_action=get_user_list&use_name_format=Y&with_bots"},
			button_name: BX.message('IM_M_MESSAGE_SEND'),
			action: BX.delegate(function (data)
			{
				var files = null;
				var text = "";
				if(typeof data == "object")
				{
					text = data.text;
					if(data.attachedFiles)
					{
						files = data.attachedFiles;
					}
				}
				else
				{
					text = data;
				}

				if(files != null && files.length>0)
				{
					var file = files[0];
					var isDiskFile = (typeof file["dataAttributes"] != "undefined");

					if(isDiskFile)
					{
						var diskFileData = file["dataAttributes"];
						var fileList = {};
						fileList[diskFileData["ID"]] =
						{
							name: diskFileData["NAME"],
							modifyDateInt: diskFileData["UPDATE_TIME"],
							sizeInt: diskFileData["SIZE"]? diskFileData["SIZE"]: 0
						}
						this.disk.uploadFromDisk(fileList, text);
					}
					else
					{
						this.disk.uploadFromMobile(files[0].base64, text);
					}
				}
				else if(text)
				{
					this.messenger.textareaHistory[this.messenger.currentTab] = '';
					this.messenger.sendMessage(this.messenger.currentTab, text);
				}

				app.clearInput();

			}, this)

		}

		if(!app.enableInVersion(17))
		{
			//backward compatibility
			delete panelParams["attachButton"]
			panelParams["plusAction"] = !this.disk.enable? "": BX.delegate(function()
			{
				this.messenger.takePhotoMenu()
			}, this)
		}

		BXMobileApp.UI.Page.TextPanel.setParams(panelParams);
		BXMobileApp.UI.Page.TextPanel.show();
		this.messenger.textPanelShowed = true;

		app.enableCaptureKeyboard(true);

		BX.bind(window, "orientationchange", BX.delegate(function(){
			if (this.messenger.popupMessengerBody.scrollHeight - this.messenger.popupMessengerBody.scrollTop < window.screen.height)
				this.messenger.autoScroll();
		}, this))

		BX.addCustomEvent("onKeyboardWillShow", BX.delegate(function()
		{
			this.keyboardShow = true;
			this.messenger.autoScroll()
		}, this))
		BX.addCustomEvent("onKeyboardDidHide", BX.delegate(function()
		{
			this.keyboardShow = false;
		}, this))

		app.pullDown({
			'enable': true,
			'pulltext': BX.message('IM_M_DIALOG_PULLTEXT'),
			'downtext': BX.message('IM_M_DIALOG_DOWNTEXT'),
			'loadtext': BX.message('IM_M_DIALOG_LOADTEXT'),
			'callback': BX.delegate(function(){
				BX.MessengerCommon.loadHistory(this.messenger.currentTab);
			}, this)
		});

		BX.addCustomEvent("onPageParamsChanged", BX.delegate(function(data){
			this.messenger.openMessenger(data.dialogId);
		}, this));

		if (BXMobileApp.apiVersion == 1)
		{
			this.messenger.openMessenger(this.messenger.currentTab);
		}
		else
		{
			BXMobileApp.UI.Page.params.get({callback:BX.delegate(function(data){
				this.messenger.openMessenger(data.dialogId);
			}, this)});
		}

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-attach-block-spoiler'}, BX.delegate(function(e) {
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

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-avatar-button'}, BX.delegate(function(e)
		{
			BX.localStorage.set('impmh', true, 1);
			var userId = BX.proxy_context.parentNode.parentNode.getAttribute('data-senderId');

			if (this.messenger.currentTab.substr(0,4) == 'chat')
			{
				var chatId = this.messenger.currentTab.substr(4);
				if (!BX.MessengerCommon.userInChat(chatId))
				{
					return false;
				}
				if (this.messenger.generalChatId == chatId && !this.messenger.canSendMessageGeneralChat)
				{
					return false;
				}
			}

			this.messenger.messageReply(userId);

			return BX.PreventDefault(e);
		}, this));

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-ajax'}, BX.delegate(function(e) {
			BX.localStorage.set('impmh', true, 1);
			if (BX.proxy_context.getAttribute('data-entity') == 'user')
			{
				 app.loadPageBlank({
					 url: this.path.profileTemplate.replace('#user_id#', BX.proxy_context.getAttribute('data-userId')),
					 bx24ModernStyle: true
				 });
			}
			else if (BX.proxy_context.getAttribute('data-entity') == 'chat')
			{
				BXMobileApp.PageManager.loadPageUnique({
					'url' : this.pathToRoot + 'mobile/im/chat.php?chat_id='+BX.proxy_context.getAttribute('data-chatId')+'&actions=Y',
					'bx24ModernStyle' : true,
					'data': {dialogId: this.currentTab}
				});
			}
			else if (BX.proxy_context.getAttribute('data-entity') == 'phoneCallHistory')
			{
				app.alert({'text': BX.message('IM_FILE_LISTEN_NA')});
			}
			return BX.PreventDefault(e);
		}, this));

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-command'}, BX.delegate(function(e) {
			BX.localStorage.set('impmh', true, 1);
			if (BX.proxy_context.getAttribute('data-entity') == 'send')
			{
				this.messenger.sendMessage(this.messenger.currentTab, BX.proxy_context.nextSibling.innerHTML);
			}
			else if (BX.proxy_context.getAttribute('data-entity') == 'put')
			{
				var putText = BX.proxy_context.nextSibling.innerHTML;
				BXMobileApp.UI.Page.TextPanel.getText(function(text){
					if (text)
					{
						putText = BX.util.trim(text)+' '+putText;
					}
					BXMobileApp.UI.Page.TextPanel.setText(putText+' ');
					BXMobileApp.UI.Page.TextPanel.focus();
				});
			}
			else if (BX.proxy_context.getAttribute('data-entity') == 'call')
			{
				this.BXIM.phoneTo(BX.proxy_context.getAttribute('data-command'));
			}
			return BX.PreventDefault(e);
		}, this));

		BX.adjust(BX('im-dialog-invite'), {children: [
			BX.create("div", { props : { className : "bx-messenger-textarea-open-invite" }, children : [
				BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
					BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
						this.popupMessengerTextareaOpenText = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text" }, html: BX.message('IM_O_INVITE_TEXT_NEW')})
					]})
				]}),
				this.popupMessengerTextareaOpenJoin = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-join bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_O_INVITE_JOIN')})
			]}),
			BX.create("div", { props : { className : "bx-messenger-textarea-open-lines" }, children : [
				BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
					BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
						this.popupMessengerTextareaOpenLinesText = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text" }, html: BX.message('IM_OL_INVITE_TEXT')})
					]})
				]}),
				BX.create("div", { props: { className : "bx-messenger-textarea-open-invite-join-box"}, children: [
					this.popupMessengerTextareaOpenLinesAnswer = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-answer bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_OL_INVITE_ANSWER')}),
					this.popupMessengerTextareaOpenLinesSkip = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-skip bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-cancel" }, html: BX.message('IM_OL_INVITE_SKIP')}),
					BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-transfer bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-transfer" }, html: BX.message('IM_OL_INVITE_TRANSFER'), events : { click: BX.delegate(function(e){ this.messenger.linesTransfer(this.messenger.currentTab.toString().substr(4)) }, this)}})
				]})
			]}),
			BX.create("div", { props : { className : "bx-messenger-textarea-general-invite" }, children : [
				BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
					BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
						this.popupMessengerTextareaGeneralText = BX.create("div", { props : { id: 'im-dialog-invite-text', className : "bx-messenger-textarea-open-invite-text" }})
					]})
				]}),
				this.popupMessengerTextareaGeneralJoin = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-join bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_G_JOIN_'+this.userGender)})
			]})
		]});

		BX.adjust(BX('im-dialog-form'), {children: [this.messenger.popupMessengerFileForm = BX.create('form', { attrs : { action : this.pathToFileAjax}, props : { className : "bx-messenger-textarea-file-form" }, children: [
			BX.create('input', { attrs : { type : 'hidden', name: 'IM_FILE_UPLOAD', value: 'Y'}}),
			this.messenger.popupMessengerFileFormChatId = BX.create('input', { attrs : { type : 'hidden', name: 'CHAT_ID', value: 0}}),
			this.messenger.popupMessengerFileFormRegChatId = BX.create('input', { attrs : { type : 'hidden', name: 'REG_CHAT_ID', value: 0}}),
			this.messenger.popupMessengerFileFormRegMessageText = BX.create('input', { attrs : { type : 'hidden', name: 'REG_MESSAGE_TEXT', value: ''}}),
			this.messenger.popupMessengerFileFormRegMessageId = BX.create('input', { attrs : { type : 'hidden', name: 'REG_MESSAGE_ID', value: 0}}),
			this.messenger.popupMessengerFileFormRegParams = BX.create('input', { attrs : { type : 'hidden', name: 'REG_PARAMS', value: ''}}),
			this.messenger.popupMessengerFileFormRegMessageHidden = BX.create('input', { attrs : { type : 'hidden', name: 'REG_MESSAGE_HIDDEN', value: 'N'}}),
			BX.create('input', { attrs : { type : 'hidden', name: 'IM_AJAX_CALL', value: 'Y'}}),
			this.messenger.popupMessengerFileFormInput = BX.create('input', { attrs : { type : 'hidden', name: 'FAKE_INPUT', value: 'Y'}})
		]})]});

		this.disk.chatDialogInit();

		BX.bind(this.popupMessengerTextareaGeneralJoin, 'click', BX.delegate(function() {
			this.settings.generalNotify = false;

			this.saveSettings({'generalNotify': this.settings.generalNotify});
			this.messenger.dialogStatusRedrawDelay();

			setTimeout(BX.delegate(function(){
				this.messenger.autoScroll();
			}, this),300);

			return true;
		}, this));

		BX.bind(this.popupMessengerTextareaOpenJoin, 'click', BX.delegate(function() {
			if (this.messenger.currentTab.substr(0, 4) != 'chat')
				return false;

			var chatId = this.messenger.currentTab.substr(4);
			BX.MessengerCommon.joinToChat(chatId);

			return true;
		}, this));

		BX.bind(this.popupMessengerTextareaOpenLinesAnswer, 'click', BX.delegate(function() {
			if (this.messenger.currentTab.substr(0, 4) != 'chat')
				return false;

			var chatId = this.messenger.currentTab.substr(4);
			if (!BX.MessengerCommon.userInChat(chatId))
			{
				var session = BX.MessengerCommon.linesGetSession(this.messenger.chat[chatId]);
				if (parseInt(session.id) <= 0)
				{
					BX.MessengerCommon.linesStartSession(chatId);
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
			if (this.messenger.currentTab.substr(0, 4) != 'chat')
				return false;

			var chatId = this.messenger.currentTab.substr(4);
			if (!BX.MessengerCommon.userInChat(chatId))
				BX.MessengerCommon.dialogCloseCurrent(true);
			else
				BX.MessengerCommon.linesSkip(chatId);

			return true;
		}, this));


		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-keyboard-button-text'}, BX.delegate(BX.MessengerCommon.clickButtonKeyboard, BX.MessengerCommon));

		/*
		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-like'}, BX.delegate(function(e) {
			BX.localStorage.set('impmh', true, 1);
			var messageId = BX.proxy_context.parentNode.parentNode.parentNode.parentNode.getAttribute('data-blockmessageid');
			BX.MessengerCommon.messageLike(messageId);
			return BX.PreventDefault(e);
		}, this));
		*/

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {tagName: 'a'}, BX.delegate(function(e) {
			BX.localStorage.set('impmh', true, 1);
		}, this));

		BX.MobileUI.addCopyableDialog(this.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-content", "bx-messenger-message", BX.delegate(function(block){
			var messageId = block.id.replace('im-message-', '');

			if (!this.messenger.message[messageId])
			{
				return false;
			}

			var messageText = this.messenger.message[messageId].textOriginal;
			if (this.messenger.message[messageId].params && this.messenger.message[messageId].params['FILE_ID'] && this.messenger.message[messageId].params['FILE_ID'].length > 0)
			{
				for (var j = 0; j < this.messenger.message[messageId].params.FILE_ID.length; j++)
				{
					var fileId = this.messenger.message[messageId].params.FILE_ID[j];
					var chatId = this.messenger.message[messageId].chatId;
					if (this.messenger.disk.files[chatId][fileId])
					{
						messageText += ' ['+BX.message('IM_F_FILE')+': '+this.messenger.disk.files[chatId][fileId].name+']';
					}
					else
					{
						messageText += ' ['+BX.message('IM_F_FILE')+']';
					}
				}
			}

			return BX.util.trim(messageText);
		}, this));

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-content'}, BX.delegate(function(e) {
			var messageId = BX.proxy_context.parentNode.getAttribute('data-blockmessageid');
			this.messenger.openMessageMenu(messageId);
		}, this));

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-reply'}, BX.delegate(function(e) {
			var chatId = BX.proxy_context.parentNode.getAttribute('data-chatid');
			var messageId = BX.proxy_context.parentNode.getAttribute('data-messageid');
			BX.MessengerCommon.joinParentChat(messageId, chatId);
		}, this));

		BX.bindDelegate(this.messenger.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-error'}, BX.delegate(function(e)
		{
			BX.localStorage.set('impmh', true, 1);
			BX.MessengerCommon.sendMessageRetry();

			return BX.PreventDefault(e);
		}, this));
	}
}

BX.ImMobile.prototype.recentPageAction = function (params)
{
	BXMobileApp.addCustomEvent("onPhoneTo", BX.delegate(function (data){
		this.phoneTo(data.number, data.params);
	}, this));

	BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", BX.delegate(function (params){
		setTimeout(BX.delegate(this.updateStateLight, this), 1000);
	}, this));

	BXMobileApp.addCustomEvent("onPull-main", BX.delegate(function (data)
	{
		if (
			typeof data.command != 'undefined'
			&& data.command == 'user_counter'
			&& typeof data.params != 'undefined'
			&& typeof data.params[BX.message('SITE_ID')] != 'undefined'
		)
		{
			this.notify.updateNotifyCounters(data.params[BX.message('SITE_ID')]);
			this.updateCounter();
		}
	}, this));

	this.show = false;
	BX.addCustomEvent("onNotificationsOpen", this.onNotificationsOpen.bind(this));
	BX.addCustomEvent("onNotificationsClose", this.onNotificationsClose.bind(this));

	var pullHandler = BX.delegate(function (dataObject)
	{
		params = dataObject.params;
		command = dataObject.command;

		if (command == 'readMessage')
		{
			this.messageCountArray[params.userId] = 0;
			this.updateCounter();
		}
		else if (command == 'readMessageChat')
		{
			this.messageCountArray['chat' + params.chatId] = 0;
			this.updateCounter();
		}
		else if (command == 'chatUserLeave')
		{
			if (params.userId == BX.message('USER_ID'))
			{
				this.messageCountArray['chat' + params.chatId] = 0;
				this.updateCounter();
			}
		}
		else if (command == 'message' || command == 'messageChat')
		{
			var userId = params.message.senderId;
			if (userId == BX.message('USER_ID'))
			{
				this.messageCountArray[params.message.recipientId] = 0;
				this.updateCounter();
				return;
			}
			if (command == 'messageChat')
				userId = params.message.recipientId;

			if (typeof(this.messageCountArray[userId]) != 'undefined')
				this.messageCountArray[userId]++;
			else
				this.messageCountArray[userId] = 1;

			app.getVar({'var': 'PAGE_ID', 'from': 'current', 'callback': BX.delegate(function (PAGE_ID)
			{
				if (PAGE_ID == 'DIALOG' + userId)
					this.messageCountArray[userId] = 0;

				this.updateCounter();
			}, this)});
		}
		else if (command == 'notifyAdd')
		{
			this.notifyCount = params.counter;
			this.updateCounter();
			this.notifyRefresh();
		}
		else if (command == 'notifyRead' || command == 'notifyUnread' || command == 'notifyConfirm')
		{
			this.notifyCount = params.counter;
			this.updateCounter();
			this.notifyRefresh();
		}

	}, this);

	BXMobileApp.addCustomEvent("onPull-im", BX.delegate(function(dataObject){

		if(dataObject.data)
		{
			for (var key in dataObject.data)
			{
				pullHandler(dataObject.data[key]);
			}
		}
		else
		{
			pullHandler(dataObject);
		}
	}, this));

	BXMobileApp.addCustomEvent("onImDialogOpen", BX.delegate(function (params)
	{
		this.messageCountArray[params.id] = 0;
		this.updateCounter();
	}, this));

	this.updateStateLight();
}

BX.ImMobile.prototype.notifyRefresh = function()
{
	clearTimeout(this.refreshTimeout);
	this.refreshTimeout = setTimeout(function(){
		app.refreshPanelPage('notifications');
		console.info('RecentList.notify: refresh');
	}, 200);

	return true;
};

BX.ImMobile.prototype.notifyRead = function(id)
{
	id = parseInt(id);
	if (id <= 0)
		return false;

	this.notify.notifyViewed(id);

	return true;
};

BX.ImMobile.prototype.onNotificationsOpen = function(params)
{
	console.info('RecentList.notify: window is open', params);

	this.notifyCount = 0;
	this.updateCounter();

	//this.notifyRead(params.lastId);
};

BX.ImMobile.prototype.onNotificationsClose = function(id)
{
	console.info('RecentList.notify: window is close, message was read ('+id+' and higher)');
	this.notifyRead(id);
};

BX.ImMobile.prototype.initPageAction = function (error)
{
	BXMobileApp.addCustomEvent("onImError", BX.delegate(function (params){
		if (error == 'AUTHORIZE_ERROR')
		{
			app.BasicAuth({success: BX.delegate(function ()
			{
				setTimeout(BX.delegate(this.updateStateLight, this), 1000);
			}, this)});
		}
		else if (error == 'RECENT_RELOAD')
		{
			app.BasicAuth({success: BX.delegate(function ()
			{
				setTimeout(BX.delegate(this.updateStateLight, this), 1000);
			}, this)});
		}
	}, this));

	var notificationHandler = BX.delegate(function (push)
	{
		var pushParams = BXMobileApp.PushManager.prepareParams(push);
		if (pushParams.TAG)
		{
			pushParams.ACTION = pushParams.TAG;
		}

		if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) == 'IM_MESS_')
		{
			var userId = parseInt(pushParams.ACTION.substr(8));
			if (userId > 0)
			{
				BXMobileApp.PageManager.loadPageUnique({
					'url' : this.pathToRoot + 'mobile/im/dialog.php'+(!app.enableInVersion(11)? "?id="+userId: ""),
					'bx24ModernStyle' : true,
					'data': {dialogId: userId}
				});
			}
		}
		else if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) == 'IM_CHAT_')
		{
			var chatId = parseInt(pushParams.ACTION.substr(8));
			if (chatId > 0)
			{
				BXMobileApp.PageManager.loadPageUnique({
					'url' : this.pathToRoot + 'mobile/im/dialog.php'+(!app.enableInVersion(11)? "?id=chat"+chatId: ""),
					'bx24ModernStyle' : true,
					'data': {dialogId: 'chat'+chatId}
				});
			}
		}
		else if (pushParams.ACTION && pushParams.ACTION.substr(0, 6) == 'IMINV_')
		{

			var arg = pushParams.ACTION.split("_");
			var userId = parseInt(arg[1]);
			var callTime = parseInt(arg[2]);
			var video = ((arg.length >=4) && arg[3] == "Y");


			var callInvite = function()
			{
				if(!mwebrtc.timesUp(callTime*1000))
				{
					BX.ready(function ()
						{
							setTimeout(function(){
								mwebrtc.callInvite(userId, video);
							}, 1500);

						}
					);
				}
			}

			if(BX.PULL.supportWebSocket())
			{
				if(BX.PULL.isWebSoketConnected())
				{
					callInvite();
				}
				else
				{
					var delayedFunction = function (status){

						if(status == "online")
						{
							callInvite();
							BX.removeCustomEvent(window, 'onPullStatus', delayedFunction);
						}

					}

					BX.addCustomEvent(window, 'onPullStatus', delayedFunction);

					setTimeout(function(){
						BX.removeCustomEvent(window, 'onPullStatus', delayedFunction);
					},15000);
				}
			}
			else
			{
				callInvite();
			}
		}
		else if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) == 'VI_CALL_')
		{
			//console.log('set', pushParams.PARAMS);
			BX.localStorage.set('viInvite', pushParams.PARAMS, 30);
		}
	}, this);

	var phoneToNumberHandler = function(){
		if(BXMobileAppContext["getLastPhoneNumber"])
		{
			if(BXMobileAppContext.getLastPhoneNumber().length > 0)
			{
				BX.MobileTools.phoneTo(BXMobileAppContext.getLastPhoneNumber(), {callMethod:"telephony"});
				BXMobileAppContext.removeLastPhoneNumber();
			}
		}
	};

	if(app.enableInVersion(15))
	{
		BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function ()
		{
			var push = BXMobileApp.PushManager.getLastNotification();
			if (push && push != {})
				notificationHandler(push);
			else
			{
				phoneToNumberHandler();
			}
		});

		notificationHandler(BXMobileApp.PushManager.getLastNotification());
		phoneToNumberHandler();
	}
	else
		BX.addCustomEvent("onOpenPush", notificationHandler);

	app.setPanelPages({
		'messages_page': this.pathToRoot + "mobile/im/index.php?NEW",
		'messages_open_empty': true,
		'notifications_page': this.pathToRoot + "mobile/im/notify.php?v2",
		'notifications_open_empty': true
	});
}

BX.ImMobile.prototype.dialogPageAction = function (params)
{
	BXMobileApp.addCustomEvent("onImError", BX.delegate(function (error){
		if (error == 'AUTHORIZE_ERROR')
		{
			app.BasicAuth({success: function(){}});
		}
	}, this));

	this.messenger.popupMessengerBody = document.body;
	this.messenger.popupMessengerBodyWrap = BX('im-dialog-wrap');
	BX.addClass(this.messenger.popupMessengerBodyWrap, 'bx-messenger-dialog-wrap');
	this.messenger.dialogOpen = true;

	clearInterval(this.serviceInterval);
	this.serviceInterval = setInterval(function(){
		BX.MessengerCommon.checkProgessMessage();
	}, 1000);
}

BX.ImMobile.prototype.updateStateLight = function ()
{
	clearTimeout(this.timeoutUpdateStateLight);

	if(this.isBackground())
	{
		//It's not necessary to update im state when the app is not active.
		return;
	}

	this.timeoutUpdateStateLight = setTimeout(BX.delegate(function ()
	{
		BX.ajax({
			url: this.pathToAjax,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 20,
			data: {'IM_UPDATE_STATE_LIGHT': 'Y', 'MOBILE': 'Y', 'FOCUS': (typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground())?'N':'Y', 'SITE_ID': BX.message('SITE_ID'), 'NOTIFY': 'Y', 'MESSAGE': 'Y', 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function (data)
			{
				BX.onCustomEvent('onUpdateStateDone', [true]);
				BXMobileApp.onCustomEvent('onUpdateStateDone', {result: true});

				if (data.ERROR.length == 0)
				{
					BX.message({'SERVER_TIME': data.SERVER_TIME});

					if (BX.PULL && data.PULL_CONFIG)
					{
						BX.PULL.updateChannelID({
							'METHOD': data.PULL_CONFIG.METHOD,
							'CHANNEL_ID': data.PULL_CONFIG.CHANNEL_ID,
							'CHANNEL_DT': data.PULL_CONFIG.CHANNEL_DT,
							'PATH': data.PULL_CONFIG.PATH,
							'LAST_ID': data.PULL_CONFIG.LAST_ID,
							'PATH_WS': data.PULL_CONFIG.PATH_WS
						});
					}

					if (data.COUNTER_MESSAGES)
						this.messageCount = parseInt(data.COUNTER_MESSAGES);
					if (data.COUNTER_NOTIFICATIONS)
						this.notifyCount = parseInt(data.COUNTER_NOTIFICATIONS);
					if (data.NOTIFY_LAST_ID)
						this.notify.notifyLastId = parseInt(data.NOTIFY_LAST_ID);

					if (this.messageCount > 0 && data.COUNTER_UNREAD_MESSAGES && typeof(data.COUNTER_UNREAD_MESSAGES) == 'object')
					{
						this.messageCount = 0;
						this.messageCountArray = {};
						for (var i in data.COUNTER_UNREAD_MESSAGES)
						{
							this.messageCount += data.COUNTER_UNREAD_MESSAGES[i].MESSAGE.counter;
							this.messageCountArray[i] = data.COUNTER_UNREAD_MESSAGES[i].MESSAGE.counter;
						}
						BX.onCustomEvent('onUpdateUserCounters', [data.COUNTER_UNREAD_MESSAGES]);
						BXMobileApp.onCustomEvent('onUpdateUserCounters', data.COUNTER_UNREAD_MESSAGES);
					}
					else
					{
						this.messageCountArray = {};
						BX.onCustomEvent('onUpdateUserCounters', [data.COUNTER_UNREAD_MESSAGES]);
						BXMobileApp.onCustomEvent('onUpdateUserCounters', data.COUNTER_UNREAD_MESSAGES);
					}
					this.updateCounter();

					if (data.COUNTERS && typeof(data.COUNTERS) == 'object')
					{
						var obZeroDate = (data.COUNTERS_ZERO_DATE && typeof(data.COUNTERS_ZERO_DATE) == 'object' ? data.COUNTERS_ZERO_DATE : null);
						BX.onCustomEvent('onImUpdateCounter', [data.COUNTERS, obZeroDate]);

						this.notify.updateNotifyCounters(data.COUNTERS);

						var obCounterTmp = BX.clone(data.COUNTERS);
						obCounterTmp.obZeroDate = obZeroDate;
						obCounterTmp.SERVER_TIME = data.SERVER_TIME;

						setTimeout(function() {
							BXMobileApp.onCustomEvent('onImUpdateCounter', obCounterTmp);
						}, 3000);
					}

					if (this.notifyCount > 0)
					{
						clearTimeout(this.notifyTimeout);
						this.notifyTimeout = setTimeout(BX.delegate(function ()
						{
							this.notify.notifyLoadFlag = true;
							app.refreshPanelPage('notifications');
						}, this), 100);
					}

					this.sendAjaxTry = 0;

					clearTimeout(this.timeoutUpdateStateLight);
					this.timeoutUpdateStateLight = setTimeout(BX.delegate(function ()
					{
						this.updateStateLight();
					}, this), this.updateStateInterval*1000);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					BX.onCustomEvent('onImError', [data.ERROR]);

					clearTimeout(this.timeoutUpdateStateLight);
					this.timeoutUpdateStateLight = setTimeout(BX.delegate(function ()
					{
						this.updateStateLight();
					}, this), 2000);
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});

					clearTimeout(this.timeoutUpdateStateLight);
					this.timeoutUpdateStateLight = setTimeout(BX.delegate(function ()
					{
						this.updateStateLight();
					}, this), 1000);
				}
				else
				{
					this.sendAjaxTry = 0;
				}
			}, this),
			onfailure: BX.delegate(function (data)
			{
				BX.onCustomEvent('onUpdateStateDone', [false]);
				BXMobileApp.onCustomEvent('onUpdateStateDone', {result: false});
				this.sendAjaxTry = 0;
			}, this)
		});
	}, this), 300);
}

BX.ImMobile.prototype.updateCounter = function ()
{
	clearTimeout(this.timeoutUpdateCounters);
	this.timeoutUpdateCounters = setTimeout(BX.delegate(function ()
	{
		this.messageCount = 0;
		for (var i in this.messageCountArray)
			this.messageCount += parseInt(this.messageCountArray[i]);

		this.logCount = 0;
		if (
			typeof this.notify != 'undefined'
			&& typeof this.notify.counters != 'undefined'
			&& typeof this.notify.counters['**'] != 'undefined'
		)
		{
			this.logCount = parseInt(this.notify.counters['**']);
		}

		app.setBadge(parseInt(this.messageCount) + parseInt(this.notifyCount) + parseInt(this.logCount));
		app.setCounters({
			'messages': parseInt(this.messageCount),
			'notifications': parseInt(this.notifyCount)
		});
	}, this), 500);
}

BX.ImMobile.prototype.isFocus = function()
{
	return false;
}
BX.ImMobile.prototype.isBackground = function()
{
	if (typeof BXMobileAppContext == "object")
	{
		if(typeof(BXMobileAppContext.isAppActive) == "function" )
			return !BXMobileAppContext.isAppActive();
		else if(typeof(BXMobileAppContext.isBackground) == "function" )
			return BXMobileAppContext.isBackground();
	}

	return false;
}



BX.ImMobile.prototype.isFocusMobile = function(func)
{
	if (this.isBackground())
	{
		func(false);
	}
	else
	{
		BXMobileApp.UI.Page.isVisible({callback: BX.delegate(function(data){
			func(data.status == 'visible');
		}, this)})
	}

	return null;
}

BX.ImMobile.prototype.isMobile = function()
{
	return false;
}

BX.ImMobile.prototype.checkRevision = function(revision)
{
	if (typeof(revision) == "number" && this.revision < revision)
	{
		console.log('NOTICE: Window reload, because REVISION UP ('+this.revision+' -> '+revision+')');
		location.reload();

		return false;
	}
	return true;
};

})();

(function() {

if (BX.ImMessengerMobile)
	return;

BX.ImMessengerMobile = function(BXIM, params)
{
	this.BXIM = BXIM;
	this.settings = {};
	this.params = params || {};

	this.notify = params.notifyClass;
	this.disk = params.diskClass;

	this.bot = params.bot;

	this.smile = params.smile;
	this.smileSet = params.smileSet;

	this.popupMessengerLikeBlock = {};
	this.popupMessengerLikeBlockTimeout = {};

	this.popupMessengerSendingTimeout = {};

	this.sendAjaxTry = 0;
	this.updateStateStepDefault = this.BXIM.ppServerStatus? parseInt(params.updateStateInterval): 60;
	this.updateStateStep = this.updateStateStepDefault;
	this.updateStateTimeout = null;

	this.readMessageTimeout = {};
	this.readMessageTimeoutSend = null;

	this.realSearchAvailable = !this.BXIM.userExtranet || !this.BXIM.bitrixIntranet && !this.BXIM.bitrix24net;
	this.realSearch = false;
	this.realSearchFound = true;

	this.users = params.users;
	for (var userId in this.users)
	{
		this.users[userId].absent = this.users[userId].absent? new Date(this.users[userId].absent): false;
		this.users[userId].idle = this.users[userId].idle? new Date(this.users[userId].idle): false;
		this.users[userId].mobile_last_date = new Date(this.users[userId].mobile_last_date);
		this.users[userId].last_activity_date = new Date(this.users[userId].last_activity_date);
	}

	this.businessUsers = params.businessUsers;
	this.openlines = params.openlines;
	this.groups = params.groups;
	this.userInGroup = params.userInGroup;
	this.redrawTab = {};
	this.loadLastMessageTimeout = {};
	this.loadLastMessageClassTimeout = {};
	this.showMessage = params.showMessage;
	this.unreadMessage = params.unreadMessage;
	this.flashMessage = params.flashMessage;
	this.history = params.history || {};

	this.openChatEnable = params.openChatEnable || true;
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

	this.dialogStatusRedrawTimeout = null;
	this.chatHeaderRedrawTimeout = null;

	this.textareaHistory = {};

	this.popupMessengerLiveChatDelayedFormMid = 0;
	this.popupMessengerLiveChatActionTimeout = null;
	this.popupMessengerLiveChatDelayedForm = null;
	this.popupMessengerLiveChatFormStage = null;

	this.mentionList = {};
	this.mentionListen = false;
	this.mentionDelimiter = '';

	this.phones = {};

	this.errorMessage = {};
	this.message = params.message;
	for (var messageId in this.message)
	{
		this.message[messageId].date = new Date(this.message[messageId].date);
	}

	this.messageTmpIndex = 0;
	this.messageCount = params.countMessage;
	this.sendMessageFlag = 0;
	this.sendMessageTmp = {};
	this.sendMessageTmpTimeout = {};

	this.popupMessenger = {'fake': true};
	this.popupMessengerTextarea = null;

	this.openChatFlag = false;
	this.popupMessengerLastMessage = 0;

	this.readedList = {};
	this.writingList = {};
	this.writingListTimeout = {};
	this.writingSendList = {};
	this.writingSendListTimeout = {};

	this.contactListPanelStatus = null;
	this.contactListSearchText = '';
	this.contactListSearchLastText = '';

	this.popupChatDialogContactListElementsType = '';
	this.popupContactListElementsWrap = null;
	this.popupContactListSearchInput = null;

	this.popupContactListElementsSize = window.screen.height;

	this.popupMessengerConnectionStatusState = "online";
	this.popupMessengerConnectionStatusStateText = "online";
	this.popupMessengerConnectionStatus = null;
	this.popupMessengerConnectionStatusText = null;
	this.popupMessengerConnectionStatusTimeout = null;

	this.recent = params.recent? params.recent: [];
	this.recentListLoad = params.recent? true: false;

	this.recentListTab = null;
	this.recentListTabCounter = null;
	this.recentListIndex = [];
	this.currentTab = 0;
	this.generalChatId = params.generalChatId;
	this.canSendMessageGeneralChat = params.canSendMessageGeneralChat;

	this.chatList = false;
	this.recentList = true;
	this.contactList = false;
	this.contactListShowed = {};

	this.contactListTab = null;
	this.contactListLoad = false;
	this.redrawContactListTimeout = {};
	this.redrawRecentListTimeout = null;

	this.enableGroupChat = this.BXIM.ppServerStatus? true: false;

	this.historySearch = '';
	this.historyOpenPage = {};
	this.historyLoadFlag = {};
	this.historyEndOfList = {};

	this.popupMessengerBody = null;
	this.popupMessengerBodyDialog = null;
	this.popupMessengerBodyAnimation = null;
	this.popupMessengerBodySize = 295;
	this.popupMessengerBodyWrap = null;

	this.popupMessengerFileForm = null;
	this.popupMessengerFileDropZone = null;
	this.popupMessengerFileButton = null;
	this.popupMessengerFileFormChatId = null;
	this.popupMessengerFileFormInput = null;

	this.linesSilentMode = {};
}

BX.ImMessengerMobile.prototype.tooltip = function(bind, text, params)
{
	if (typeof(text) == 'object')
	{
		text = text.outerHTML;
	}

	(new BXMobileApp.UI.NotificationBar({
		message: text,
		contentType: 'html',
		color:"#af000000",
		textColor: "#ffffff",
		groupId: 'im-tooltip',
		maxLines: 4,
		align: "left",
		indicatorHeight: 30,
		isGlobal:true,
		useCloseButton:true,
		hideOnTap:true
	}, 'im-tooltip')).show();
}

BX.ImMessengerMobile.prototype.newMessage = function()
{
	var arNewMessage = [];
	var arNewMessageText = [];
	var flashCount = 0;
	var flashNames = {};

	for (var i in this.flashMessage)
	{
		var skip = false;
		var skipBlock = false;

		if (i == this.currentTab)
		{
			skip = true;
		}
		else if (i.toString().substr(0,4) == 'chat' && this.userChatBlockStatus[i.substr(4)] && this.userChatBlockStatus[i.substr(4)][this.BXIM.userId])
		{
			skipBlock = true;
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

		for (var k in this.flashMessage[i])
		{
			if (this.flashMessage[i][k] !== false)
			{
				var isChat = this.message[k].recipientId.toString().substr(0,4) == 'chat';
				var recipientId = this.message[k].recipientId;

				var senderId = !isChat && this.message[k].senderId == 0? i: this.message[k].senderId;
				var messageText = this.message[k].text_mobile? this.message[k].text_mobile: this.message[k].text;
				if (i != this.BXIM.userId)
				{
					if (isChat)
					{
						if (this.chat[recipientId.substr(4)])
						{
							flashNames[i] = this.chat[recipientId.substr(4)].name;
						}
					}
					else
					{
						if (this.users[senderId])
						{
							flashNames[i] = this.users[senderId].name;
						}
					}
				}

				messageText = messageText.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "["+BX.message("IM_M_QUOTE_BLOCK")+"]");
				if (messageText.length > 150)
				{
					messageText = messageText.substr(0, 150);
					var lastSpace = messageText.lastIndexOf(' ');
					if (lastSpace < 140)
						messageText = messageText.substr(0, lastSpace)+'...';
					else
						messageText = messageText.substr(0, 140)+'...';
				}

				if (messageText == '' && this.message[k].params['FILE_ID'].length > 0)
				{
					messageText = '['+BX.message('IM_F_FILE')+']';
				}

				messageText = messageText.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/gi, function(whole, userId, text) {return text;});
				messageText = messageText.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, function(whole, historyId, text) {return text;});

				var avatarType = 'private';
				var avatarImage = isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar;
				if (isChat)
				{
					if (recipientId.substr(4) == this.generalChatId)
					{
						avatarType = 'general';
					}
					else
					{
						avatarType = this.chat[recipientId.substr(4)].type;
					}
				}

				arNewMessageText.push({
					'id':  isChat? recipientId: senderId,
					'title':  isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name,
					'text':  (isChat && senderId>0?this.users[senderId].name+': ':'')+messageText,
					'icon':  BX.MessengerCommon.isBlankAvatar(avatarImage)? BX.MessengerCommon.getDefaultAvatar(avatarType): avatarImage,
					'tag':  'im-messenger-'+(isChat? recipientId: senderId)
				});

				this.flashMessage[i][k] = false;
			}
		}
	}

	if (arNewMessageText.length > 2)
	{
		var countMessage = arNewMessageText.length;
		var names = '';
		for (var i in flashNames)
			names += ', <i>'+flashNames[i]+'</i>';

		arNewMessageText = []
		arNewMessageText.push({
			'id': 'im-common',
			'title':  BX.message('IM_NM_MESSAGE_1').replace('#COUNT#', countMessage),
			'icon': BX.MessengerCommon.getDefaultAvatar('notify'),
			'text':  BX.message('IM_NM_MESSAGE_2').replace('#USERS#', BX.util.htmlspecialcharsback(names.substr(2))).replace(/<\/?[^>]+>/gi, ''),
			'tag': 'im-messenger'
		})
	}
	else if (arNewMessageText.length == 0)
	{
		return false;
	}

	for (var i = 0; i < arNewMessageText.length; i++)
	{
		var tapFunction = function(){};
		if (arNewMessageText[i].tag == 'im-messenger')
		{
			tapFunction = function(){
				BXMobileApp.UI.Slider.setState(BXMobileApp.UI.Slider.state.RIGHT);
			};
		}
		else
		{
			tapFunction = BX.proxy(function(data){
				this.openMessenger(data.extra.dialogId);
			}, this);
		}

		(new BXMobileApp.UI.NotificationBar({
			message: '<b>'+arNewMessageText[i].title+"</b><br>"+arNewMessageText[i].text,
			contentType: 'html',
			color:"#af000000",
			textColor: "#ffffff",
			groupId: arNewMessageText[i].tag,
			maxLines: 4,
			align: "left",
			imageURL: arNewMessageText[i].icon,
			imageBorderRadius: 50,
			indicatorHeight: 30,
			isGlobal:true,
			useCloseButton:true,
			autoHideTimeout: 5000,
			hideOnTap:true,
			onTap: tapFunction,
			extra: {'dialogId': arNewMessageText[i].id}
		}, arNewMessageText[i].id)).show();
	}
};

BX.ImMessengerMobile.prototype.drawRecentList = function()
{
	app.pullDown({
		'enable': true,
		'pulltext': BX.message('IM_PULLDOWN_RL_1'),
		'downtext': BX.message('IM_PULLDOWN_RL_2'),
		'loadtext': BX.message('IM_PULLDOWN_RL_3'),
		'callback': function(){
			app.BasicAuth({
				success: function() {
					//BX.onCustomEvent('onImError', [{error: 'RECENT_RELOAD'}]);
					//BXMobileApp.onCustomEvent('onImError', {error: 'RECENT_RELOAD'});
					//BX.frameCache.update();
					app.pullDownLoadingStop();
					BXMobileApp.UI.Page.reload();
				},
				failture: function() {
					app.pullDownLoadingStop();
				}
			});
		}
	});

	this.popupContactListWrap = BX('im-contact-list-search');
	this.popupContactListWrap.innerHTML = '';
	BX.addClass(this.popupContactListWrap, 'bx-messenger-cl-wrap');
	BX.unbindAll(this.popupContactListWrap);

	BX.adjust(this.popupContactListWrap, {children: [
		BX.create("div", { props : { className : "bx-messenger-cl-search"+(this.webrtc.phoneEnabled? ' bx-messenger-cl-search-with-call': '') }, children : [
			this.webrtc.phoneEnabled? this.popupContactListSearchCall = BX.create("span", {props : { className : "bx-messenger-cl-switcher-tab-wrap bx-messenger-input-search-call" }, html: '<span class="bx-messenger-input-search-call-icon"></span>'}): null,
			BX.create("div", { props : { className : "bx-messenger-input-wrap bx-messenger-cl-search-wrap" }, children : [
				this.popupContactListSearchClose = BX.create("span", {props : { className : "bx-messenger-input-close" }}),
				this.popupContactListSearchInput = BX.create("input", { attrs: {type: "text", placeholder: BX.message('IM_SEARCH_PLACEHOLDER_CP'), value: this.contactListSearchText}, props : { className : "bx-messenger-input" }})
			]})
		]})
	]});
	BX.unbindAll(this.popupContactListSearchInput);
	BX.bind(this.popupContactListSearchInput, "focus", BX.delegate(function() {
		if (this.contactListSearchText.length == 0 && !this.chatList)
		{
			BX.MessengerCommon.chatListRedraw();
		}
	}, this));
	BX.bind(this.popupContactListSearchInput, "keyup", BX.delegate(function(e)
	{
		BX.MessengerCommon.contactListSearch(e)
	}, this));

	if (this.webrtc.phoneEnabled)
	{
		BX.unbindAll(this.popupContactListSearchCall);
		BX.bind(this.popupContactListSearchCall, "click", function(){
			BX.MobileCallUI.numpad.show();
		});
	}

	this.popupContactListElementsWrap = BX('im-contact-list-wrap');
	this.popupContactListElementsWrap.innerHTML = '';
	BX.unbindAll(this.popupContactListElementsWrap);

	BX.addClass(this.popupContactListElementsWrap, 'bx-messenger-recent-wrap');

	BX.unbindAll(this.popupContactListSearchClose);
	BX.bind(this.popupContactListSearchClose, "click", BX.delegate(BX.MessengerCommon.contactListSearchClear, BX.MessengerCommon));

	if (this.recent.length == 0)
	{
		BX.MessengerCommon.chatListRedraw();
	}
	else
	{
		BX.MessengerCommon.userListRedraw();
	}
}

BX.ImMessengerMobile.prototype.openPhotoGallery = function(currentPhoto)
{
	var nodes = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-file-image-src");
	var photos = [];

	for(var i = 0; i < nodes.length; i++)
	{
		var chatId = nodes[i].getAttribute('data-chatId');
		var diskId = nodes[i].getAttribute('data-diskId');

		if (
			chatId && diskId
			&& this.disk.files[chatId] && this.disk.files[chatId][diskId]
		)
		{
			var file = this.disk.files[chatId][diskId];
			if (file.type != 'image')
				continue;

			photos.push({
				'url': file.urlShow,
				'description': file.name
			});
		}
		else
		{
			var node = BX.findChildByClassName(nodes[i], "bx-messenger-file-image-text");
			photos.push({
				'url': node.getAttribute('src'),
				'description': ''
			});
		}
	}

	if (photos.length > 0)
	{
		BX.localStorage.set('impmh', true, 1);
		BXMobileApp.UI.Photo.show({photos: photos, default_photo: currentPhoto})
	}
}

BX.ImMessengerMobile.prototype.dialogStatusRedraw = function(params)
{
	if (this.BXIM.mobileAction != 'DIALOG')
		return false;

	var paramsType = params && params.type? parseInt(params.type): 'none';

	clearTimeout(this.dialogStatusRedrawTimeout);
	this.dialogStatusRedrawTimeout = setTimeout(BX.delegate(function(){
		this.dialogStatusRedrawDelay(params)
	}, this), 200);
}

BX.ImMessengerMobile.prototype.dialogStatusRedrawDelay = function(params)
{
	params = params || {};
	if (this.currentTab == 0)
		return false;

	window.PAGE_ID = "DIALOG"+this.currentTab;

	this.openChatFlag = false;
	this.openCallFlag = false;
	this.openLinesFlag = false;

	if (this.currentTab.toString().substr(0,4) == 'chat')
	{
		this.openChatFlag = true;
		if (this.chat[this.currentTab.toString().substr(4)] && this.chat[this.currentTab.toString().substr(4)].type == 'call')
			this.openCallFlag = true;
		else if (this.chat[this.currentTab.toString().substr(4)] && this.chat[this.currentTab.toString().substr(4)].type == 'lines')
			this.openLinesFlag = true;
	}

	if (this.openChatFlag)
	{
		var chatId = this.currentTab.toString().substr(4);
		if (this.chat[chatId] && this.chat[chatId].type != 'call')
		{
			var muteButtonText = this.userChatBlockStatus[chatId] && this.userChatBlockStatus[chatId][this.BXIM.userId]? BX.message('IM_CHAT_MUTE_ON'): BX.message('IM_CHAT_MUTE_OFF');

			var items = [];
			if (!(this.chat[chatId].type == 'lines' || this.chat[chatId].type == 'livechat') && BX.MessengerCommon.userInChat(chatId))
			{
				items.push({ icon: 'glasses', name: muteButtonText, action:BX.delegate(function() {  BX.MessengerCommon.muteMessageChat(this.currentTab); }, this)});
			}
			items.push({ icon: 'user', name: BX.message('IM_M_MENU_USERS'), action:BX.delegate(function() {
				BXMobileApp.PageManager.loadPageUnique({
					'url' : this.BXIM.pathToRoot + 'mobile/im/chat.php?chat_id='+this.currentTab.toString().substr(4),
					'bx24ModernStyle' : true,
					'data': {dialogId: this.currentTab}
				})
			}, this)});

			if (this.chat[chatId].type == 'livechat')
			{

			}
			else if (this.chat[chatId].type == 'lines')
			{
				var chatId = this.currentTab.toString().substr(4);
				var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);

				if (this.chat[chatId].owner > 0)
				{
					items.push({ icon: 'add', name: BX.message('IM_M_MENU_ADD'), action:BX.delegate(function() {  this.extendChat(this.currentTab, true, true); }, this)});
					if (this.chat[chatId].owner == this.BXIM.userId)
					{
						items.push({ icon: 'transfer', name: BX.message('IM_OL_INVITE_TRANSFER'), action:BX.delegate(function() {  this.linesTransfer(chatId); }, this)});
					}
					items.push({ name: BX.message(this.linesSilentMode[chatId]? "IM_M_OL_SILENT_OFF": "IM_M_OL_SILENT_ON"), action:BX.delegate(function() {  this.linesToggleSilentMode(); }, this)});
				}
				if (this.chat[chatId].owner == this.BXIM.userId)
				{
					items.push({ icon: 'pause', name: BX.message(session.pin == "Y"? "IM_M_OL_ASSIGN_OFF": "IM_M_OL_ASSIGN_ON"), action:BX.delegate(function() {  this.linesTogglePinMode(); }, this)});
					if (session.crm != 'Y')
					{
						items.push({ name: BX.message('IM_M_OL_ADD_LEAD'), action:BX.delegate(function() {  this.linesCreateLead(); }, this)});
					}
					items.push({ name: BX.message('IM_M_OL_CLOSE'), action:BX.delegate(function() {  this.linesCloseDialog(); }, this)});
				}
				if (session.crmLink)
				{
					items.push({ name: BX.message('IM_M_OL_GOTO_CRM'), action:BX.delegate(function() {
						var params = BX.MobileTools.getMobileUrlParams(session.crmLink);
						if (params)
						{
							BXMobileApp.PageManager.loadPageBlank(params);
						}
					}, this)});
				}
				if (this.chat[chatId].owner == 0)
				{
					items.push({ name: BX.message('IM_M_OL_SPAM'), action:BX.delegate(function() {  this.linesMarkAsSpam(); }, this)});
				}
			}
			else if (!BX.MessengerCommon.checkRestriction(chatId, 'EXTEND') && BX.MessengerCommon.userInChat(chatId))
			{
				items.push({ icon: 'add', name: BX.message('IM_M_MENU_ADD'), action:BX.delegate(function() {  this.extendChat(this.currentTab, true); }, this)});
			}

			items.push({ icon: 'reload', name: BX.message('IM_M_MENU_RELOAD'), action:function() {
				BXMobileApp.UI.Page.TopBar.title.setText('');
				BXMobileApp.UI.Page.TopBar.title.setDetailText('');
				location.reload();
			}});
			if (this.chat[chatId].type == 'livechat' || BX.MessengerCommon.checkRestriction(chatId, 'LEAVE'))
			{

			}
			else if (this.chat[chatId].type == 'lines')
			{
				if (this.chat[chatId].owner > 0 && this.chat[chatId].owner != this.BXIM.userId)
				{
					items.push({ icon: 'cross', name: BX.message('IM_M_MENU_LEAVE'), action:BX.delegate(function() {
						this.BXIM.openConfirm({title: BX.message('IM_MENU_WARN'), message: BX.message('IM_MENU_LEAVE_CONFIRM'), params: {chatId: chatId}}, [
							{text: BX.message('IM_MENU_MESS_DEL_YES'), callback: function(params){ BX.MessengerCommon.leaveFromChat(params.chatId); }},
							{text: BX.message('IM_MENU_CANCEL')}
						]);
					}, this)});
				}
			}
			else if (BX.MessengerCommon.userInChat(chatId))
			{
				items.push({ icon: 'cross', name: BX.message('IM_M_MENU_LEAVE'), action:BX.delegate(function() {
					this.BXIM.openConfirm({title: BX.message('IM_MENU_WARN'), message: BX.message('IM_MENU_LEAVE_CONFIRM'), params: {chatId: chatId}}, [
						{text: BX.message('IM_MENU_MESS_DEL_YES'), callback: function(params){ BX.MessengerCommon.leaveFromChat(params.chatId); }},
						{text: BX.message('IM_MENU_CANCEL')}
					]);
				}, this)});
			}

			app.menuCreate({useNavigationBarColor: true, items:items});
		}
		else
		{
			app.menuCreate({
				useNavigationBarColor: true,
				items: [
					{ icon: 'audio', name: BX.message('IM_AUDIO_CALL'), action:BX.delegate(function() {
						this.BXIM.phoneTo(this.chat[chatId].call_number);
					}, this)},
					{
						icon: 'reload', name: BX.message('IM_M_MENU_RELOAD'), action: function ()
						{
							BXMobileApp.UI.Page.TopBar.title.setText('');
							BXMobileApp.UI.Page.TopBar.title.setDetailText('');
							//BXMobileApp.UI.Page.reloadUnique()
							location.reload();
						}
					}
				]
			});
		}
		if (this.chat[chatId])
		{
			var color = '';
			if (this.chat[chatId].type == 'lines')
			{
				color = '#16938b';
			}
			else
			{
				color = this.chat[chatId].extranet? '#e8a441': this.chat[chatId].color;
			}
		}
	}
	else if (this.currentTab)
	{
		var userId = this.currentTab;

		var sheetButtons = [];

		var userData = {};

		BX.MessengerCommon.getUserParam(this.BXIM.userId);
		if (this.users[this.BXIM.userId])
		{
			userData[this.BXIM.userId] = BX.util.objectClone(this.users[this.BXIM.userId]);
			if (userData[this.BXIM.userId].name)
			{
				userData[this.BXIM.userId].name = BX.util.htmlspecialcharsback(userData[this.BXIM.userId].name);
			}
			if (userData[this.BXIM.userId].last_name)
			{
				userData[this.BXIM.userId].last_name = BX.util.htmlspecialcharsback(userData[this.BXIM.userId].last_name);
			}
			if (userData[this.BXIM.userId].first_name)
			{
				userData[this.BXIM.userId].first_name = BX.util.htmlspecialcharsback(userData[this.BXIM.userId].first_name);
			}
			if (userData[this.BXIM.userId].work_position)
			{
				userData[this.BXIM.userId].work_position = BX.util.htmlspecialcharsback(userData[this.BXIM.userId].work_position);
			}
		}

		BX.MessengerCommon.getUserParam(userId);
		if (this.users[userId])
		{
			userData[userId] = BX.util.objectClone(this.users[userId]);
			if (userData[userId].name)
			{
				userData[userId].name = BX.util.htmlspecialcharsback(userData[userId].name);
			}
			if (userData[userId].last_name)
			{
				userData[userId].last_name = BX.util.htmlspecialcharsback(userData[userId].last_name);
			}
			if (userData[userId].first_name)
			{
				userData[userId].first_name = BX.util.htmlspecialcharsback(userData[userId].first_name);
			}
			if (userData[userId].work_position)
			{
				userData[userId].work_position = BX.util.htmlspecialcharsback(userData[userId].work_position);
			}
		}

		if (this.BXIM.userId != userId && this.users[userId] && !this.users[userId].bot && !this.users[userId].network)
		{
			var phoneCount = BX.MessengerCommon.countObject(this.phones[userId]);
			if (phoneCount > 0)
			{
				sheetButtons.push({
					title: BX.message("IM_AUDIO_CALL"),
					callback: BX.delegate(function () {
						BXMobileApp.onCustomEvent("onCallInvite", {"userId": userId, video: false, userData: userData},true);
					}, this)
				});

				if (this.phones[userId].PERSONAL_MOBILE)
				{
					sheetButtons.push({
						title: BX.message("IM_PHONE_MOB")+": "+this.phones[userId].PERSONAL_MOBILE,
						callback: BX.delegate(function () {
							this.BXIM.phoneTo(this.phones[userId].PERSONAL_MOBILE);
						}, this)
					});
				}
				if (this.phones[userId].WORK_PHONE)
				{
					sheetButtons.push({
						title: BX.message("IM_PHONE_WORK")+": "+this.phones[userId].WORK_PHONE,
						callback: BX.delegate(function () {
							this.BXIM.phoneTo(this.phones[userId].WORK_PHONE);
						}, this)
					});
				}
				if (this.phones[userId].PERSONAL_PHONE)
				{
					sheetButtons.push({
						title: BX.message("IM_PHONE_DEF")+": "+this.phones[userId].PERSONAL_PHONE,
						callback: BX.delegate(function () {
							this.BXIM.phoneTo(this.phones[userId].PERSONAL_PHONE);
						}, this)
					});
				}
				if (this.phones[userId].INNER_PHONE && this.webrtc.phoneEnabled)
				{
					sheetButtons.push({
						title: BX.message("IM_PHONE_DEF")+": "+this.phones[userId].INNER_PHONE,
						callback: BX.delegate(function () {
							this.BXIM.phoneTo(this.phones[userId].INNER_PHONE, {callMethod: 'telephony'});
						}, this)
					});
				}
			}
		}

		var menuItems = [];
		menuItems.push({ icon: 'user', name: BX.message('IM_M_MENU_USER'), action:BX.delegate(function() { app.loadPageBlank({url: this.BXIM.path.profileTemplate.replace('#user_id#', this.currentTab), bx24ModernStyle: true});}, this)});
		menuItems.push({ icon: 'add', name: BX.message('IM_M_MENU_ADD'), action:BX.delegate(function() {  this.extendChat(this.currentTab, false); }, this)});

		if (this.BXIM.userId != userId && this.users[userId] && !this.users[userId].bot && !this.users[userId].network)
		{
			if (sheetButtons.length > 1)
			{
				var callSheet = new BXMobileApp.UI.ActionSheet({buttons: sheetButtons},"call_audio");
				menuItems.push({ icon: 'audio', name: BX.message('IM_AUDIO_CALL'), action:BX.delegate(function() {
					callSheet.show();
				}, this)});
			}
			else
			{
				menuItems.push({ icon: 'audio', name: BX.message('IM_AUDIO_CALL'), action:BX.delegate(function() {
					BXMobileApp.onCustomEvent("onCallInvite", {"userId": userId, video: false, userData: userData},true);
				}, this)});
			}

			menuItems.push({ icon: 'video', name: BX.message('IM_VIDEO_CALL_LIST'), action:BX.delegate(function() {
				BXMobileApp.onCustomEvent("onCallInvite", {"userId": this.currentTab, video: true, userData: userData}, true);
			}, this)});
		}

		menuItems.push({ icon: 'reload', name: BX.message('IM_M_MENU_RELOAD'), action:function() {
			BXMobileApp.UI.Page.TopBar.title.setText('');
			BXMobileApp.UI.Page.TopBar.title.setDetailText('');
			//BXMobileApp.UI.Page.reloadUnique()
			location.reload();
		}});


		app.menuCreate({useNavigationBarColor: true, items:menuItems});
		if (this.users[userId])
		{
			var color = this.users[userId].extranet? '#e8a441': this.users[userId].color;
		}
	}

	if (app.enableInVersion(10))
	{
		clearInterval(this.popupMessengerPanelLastDateInterval);

		if (this.openChatFlag && this.chat[chatId])
		{
			this.redrawChatHeaderDelay();
		}
		else if (this.users[userId])
		{
			BXMobileApp.UI.Page.TopBar.title.setText(BX.util.htmlspecialcharsback(this.users[userId].name));
			BXMobileApp.UI.Page.TopBar.title.setImage(BX.MessengerCommon.isBlankAvatar(this.users[userId].avatar)? BX.MessengerCommon.getDefaultAvatar('private'): this.users[userId].avatar);

			var funcUpdateLastDate = BX.delegate(function() {
				var detailText = BX.MessengerCommon.getUserPosition(this.users[userId], true);
				BXMobileApp.UI.Page.TopBar.title.setDetailText(detailText);
			}, this);
			funcUpdateLastDate();
			this.popupMessengerPanelLastDateInterval = setInterval(funcUpdateLastDate, 60000);
		}
		BXMobileApp.UI.Page.TopBar.title.setCallback(function () {
			app.menuShow();
		});
		BXMobileApp.UI.Page.TopBar.title.show();
	}
	else
	{
		app.addButtons({
			addRefreshButton:{
				type: 'context-menu',
				style: 'custom',
				callback:function(){
					app.menuShow();
				}
			}
		});
	}

	if (this.popupMessengerFileFormChatId)
	{
		if (this.openChatFlag)
			this.popupMessengerFileFormChatId.value = chatId;
		else
			this.popupMessengerFileFormChatId.value = this.userChat[this.currentTab]? this.userChat[this.currentTab]: 0;
	}

	var addClass = [];
	var removeClass = [];
	if (this.openChatFlag)
	{
		if (this.generalChatId == chatId)
		{
			if (!this.BXIM.popupMessengerTextareaGeneralText)
			{
				this.BXIM.popupMessengerTextareaGeneralText = BX('im-dialog-invite-text');
			}
			if (!this.canSendMessageGeneralChat)
			{
				if (this.textPanelShowed)
				{
					this.textPanelShowed = false;
					BXMobileApp.UI.Page.TextPanel.hide();
				}
				this.BXIM.popupMessengerTextareaGeneralText.innerHTML = BX.message('IM_G_ACCESS');
				addClass.push('bx-messenger-chat-general-access');
				removeClass.push('bx-messenger-chat-general-first-open');
			}
			else if (this.BXIM.settings.generalNotify)
			{
				if (this.textPanelShowed)
				{
					this.textPanelShowed = false;
					BXMobileApp.UI.Page.TextPanel.hide();
				}
				this.BXIM.popupMessengerTextareaGeneralText.innerHTML = BX.message('IM_G_JOIN')
					.replace('#LINK_START#', '<a href="'+BX.message('IM_G_JOIN_LINK')+'" target="_blank" style="margin-left: 10px; text-decoration: underline;">')
					.replace('#LINK_END#', '</a>')
					.replace('#ICON#', '<span class="bx-messenger-icon-notify-mute" onclick="BX.MessengerCommon.muteMessageChat(\'chat'+this.generalChatId+'\');"></span>')
				;
				removeClass.push('bx-messenger-chat-general-access');
				addClass.push('bx-messenger-chat-general-first-open');
			}
			else
			{
				if (!this.textPanelShowed)
				{
					this.textPanelShowed = true;
					BXMobileApp.UI.Page.TextPanel.show();
				}
				removeClass.push('bx-messenger-chat-general-first-open');
				removeClass.push('bx-messenger-chat-general-access');
			}
			removeClass.push('bx-messenger-chat-guest');
			removeClass.push('bx-messenger-chat-lines');
		}
		else
		{
			removeClass.push('bx-messenger-chat-general-first-open');
			removeClass.push('bx-messenger-chat-general-access');
			removeClass.push('bx-messenger-chat-lines');

			if (this.chat[chatId] && this.chat[chatId].fake)
			{
			}
			else if (BX.MessengerCommon.userInChat(chatId))
			{
				if (this.chat[chatId].type == 'lines' && this.chat[chatId].owner == 0)
				{
					if (this.textPanelShowed)
					{
						this.textPanelShowed = false;
						BXMobileApp.UI.Page.TextPanel.hide();
					}
					addClass.push('bx-messenger-chat-guest');
					addClass.push('bx-messenger-chat-lines');
				}
				else
				{
					if (!this.textPanelShowed)
					{
						this.textPanelShowed = true;
						BXMobileApp.UI.Page.TextPanel.show();
					}
					removeClass.push('bx-messenger-chat-guest');
				}
			}
			else
			{
				if (this.textPanelShowed)
				{
					this.textPanelShowed = false;
					BXMobileApp.UI.Page.TextPanel.hide();
				}
				addClass.push('bx-messenger-chat-guest');
			}
		}
	}
	else
	{
		removeClass.push('bx-messenger-chat-general-first-open');
		removeClass.push('bx-messenger-chat-general-access');
		removeClass.push('bx-messenger-chat-guest');
		removeClass.push('bx-messenger-chat-lines');
		if (!this.textPanelShowed)
		{
			this.textPanelShowed = true;
			BXMobileApp.UI.Page.TextPanel.show();
		}
	}

	BX.removeClass(BX('im-dialog-invite'), removeClass.join(" "));
	BX.addClass(BX('im-dialog-invite'), addClass.join(" "));
}

BX.ImMessengerMobile.prototype.autoScroll = function ()
{
	if (document.body.scrollHeight <= window.innerHeight)
		return false;

	this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight

	return true;
}

BX.ImMessengerMobile.prototype.takePhotoMenu = function ()
{
	var action = new BXMobileApp.UI.ActionSheet({
		buttons: [
				{
					title: BX.message('IM_MENU_UPLOAD_PHOTO'),
					callback: BX.delegate(function()
					{
						app.takePhoto({
							quality: 80,
							source: 1,
							correctOrientation: true,
							targetWidth: 1024,
							targetHeight: 1024,
							destinationType: Camera.DestinationType.DATA_URL,
							callback: BX.delegate(this.disk.uploadFromMobile, this.disk)
						});
					}, this)
				},
				{
					title: BX.message('IM_MENU_UPLOAD_GALLERY'),
					callback: BX.delegate(function()
					{
						app.takePhoto({
							quality: 80,
							targetWidth: 1024,
							targetHeight: 1024,
							destinationType: Camera.DestinationType.DATA_URL,
							callback: BX.delegate(this.disk.uploadFromMobile, this.disk)
						});
					}, this)
				}
			]
		},
		"textPanelSheet"
	);
	action.show();
}

BX.ImMessengerMobile.prototype.updateChatAvatar = function(chatId, chatAvatar)
{
	if (!this.openChatFlag)
		return false;

	var currentChatId = this.currentTab.toString().substr(4);
	if (chatId != currentChatId)
		return false;

	if (app.enableInVersion(10))
	{
		if (BX.MessengerCommon.isBlankAvatar(chatAvatar))
		{
			this.redrawChatHeaderDelay();
		}
		else
		{
			BXMobileApp.UI.Page.TopBar.title.setImage(chatAvatar);
		}
	}
}

BX.ImMessengerMobile.prototype.textareaIconDialogClick = function()
{
	app.alert({'text': BX.message('IM_FUNCTION_FOR_BROWSER')});
}

BX.ImMessengerMobile.prototype.redrawChatHeader = function()
{
	clearTimeout(this.chatHeaderRedrawTimeout);
	this.chatHeaderRedrawTimeout = setTimeout(BX.delegate(function(){
		this.redrawChatHeaderDelay()
	}, this), 200);
}

BX.ImMessengerMobile.prototype.redrawChatHeaderDelay = function()
{
	if (!this.openChatFlag)
		return false;

	var chatId = this.currentTab.toString().substr(4);
	if (!this.chat[chatId])
		return false;

	if (this.popupMessengerFileFormChatId)
	{
		this.popupMessengerFileFormChatId.value = chatId;
	}

	if (app.enableInVersion(10))
	{
		var avatarType = this.chat[chatId].type;
		BXMobileApp.UI.Page.TopBar.title.setText(BX.util.htmlspecialcharsback(this.chat[chatId].name));

		if (this.chat[chatId].type == 'call')
		{
			BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_VI_CALL"));
		}
		else if (this.chat[chatId].type == 'lines')
		{
			BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_LINES"));
			// TODO type of connector
		}
		else if (this.chat[chatId].type == 'livechat')
		{
			BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_LINES"));
		}
		else
		{
			if (this.generalChatId == chatId && this.userInChat[chatId])
			{
				avatarType = 'general';
				BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_M_MENU_USERS")+": "+(this.userInChat[chatId].length));
			}
			else if (this.chat[chatId].type == 'open')
			{
				BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_CL_OPEN_CHAT_NEW"));
			}
			else
			{
				BXMobileApp.UI.Page.TopBar.title.setDetailText(BX.message("IM_CL_CHAT_NEW"));
			}
		}
		BXMobileApp.UI.Page.TopBar.title.setImage(BX.MessengerCommon.isBlankAvatar(this.chat[chatId].avatar)? BX.MessengerCommon.getDefaultAvatar(avatarType): this.chat[chatId].avatar);
	}
	var color = '';
	if (this.chat[chatId].type == 'lines')
	{
		color = '#16938b';
	}
	else
	{
		color = this.chat[chatId].extranet? '#e8a441': this.chat[chatId].color;
	}
}

BX.ImMessengerMobile.prototype.extraClose = function() // for exit from chat
{
	app.closeController();
}

BX.ImMessengerMobile.prototype.openMessenger = function(userId, node, openPage)
{
	if (this.BXIM.mobileAction == 'RECENT')
	{
		openPage = openPage !== false;

		if (this.currentTab != userId)
		{
			var selectedElements = BX.findChild(this.popupContactListElementsWrap, {attribute : {'data-userId' : this.currentTab}}, false);
			if (selectedElements)
			{
				BX.removeClass(selectedElements, "bx-messenger-cl-item-active");
			}
			if (!node)
			{
				selectedElements = BX.findChild(this.popupContactListElementsWrap, {attribute : {'data-userId' : userId}}, false);
				if (selectedElements)
				{
					node = selectedElements;
				}
			}
			if (node)
			{
				BX.addClass(node, "bx-messenger-cl-item-active");
			}

			this.currentTab = userId;
		}
		if (openPage)
		{
			BXMobileApp.PageManager.loadPageUnique({
				'url' : this.BXIM.pathToRoot + 'mobile/im/dialog.php'+(!app.enableInVersion(11)? "?id="+this.currentTab: ""),
				'bx24ModernStyle' : true,
				'data': {dialogId: this.currentTab}
			})
		}
	}
	else if (this.BXIM.mobileAction == 'DIALOG')
	{
		if (!this.BXIM.messenger.redrawTab[userId] && this.currentTab == userId && this.popupMessengerBodyWrap.innerHTML != '')
			return false;

		if (typeof(userId) == "undefined" || userId == null)
			userId = 0;

		if (this.currentTab == null)
			this.currentTab = 0;

		this.openChatFlag = false;
		this.openNetworkFlag = false;
		this.openCallFlag = false;
		this.openLinesFlag = false;

		if (userId.toString().substr(0,4) == 'chat')
		{
			this.openChatFlag = true;
			BX.MessengerCommon.getUserParam(userId);
			if (this.chat[userId.toString().substr(4)] && this.chat[userId.toString().substr(4)].type == 'call')
				this.openCallFlag = true;
			else if (this.chat[userId.toString().substr(4)] && this.chat[userId.toString().substr(4)].type == 'lines')
				this.openLinesFlag = true;
		}
		else if (userId.toString().substr(0,7) == 'network')
		{
			this.openNetworkFlag = true;
			BX.MessengerCommon.getUserParam(userId);
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

		if (userId == 0)
		{
			this.openChatFlag = false;
			app.closeController();
		}
		else if (this.openChatFlag || this.openNetworkFlag || userId > 0)
		{
			this.currentTab = userId;
			BX.MessengerCommon.openDialog(this.currentTab);
		}
	}
}

BX.ImMessengerMobile.prototype.closeMessenger = function(dialogId)
{
	dialogId = dialogId? dialogId: this.currentTab;

	this.currentTab = 0;
	this.openChatFlag = false;

	var selectedElements = BX.findChild(this.popupContactListElementsWrap, {attribute: {'data-userId': dialogId}}, false);
	if (selectedElements)
	{
		if (BX.hasClass(selectedElements, "bx-messenger-cl-item-active"))
		{
			BX.removeClass(selectedElements, "bx-messenger-cl-item-active");
		}
	}
}

BX.ImMessengerMobile.prototype.closeMenuPopup = function()
{
}

BX.ImMessengerMobile.prototype.sendMessage = function(recipientId, text)
{
	recipientId = typeof(recipientId) == 'string' || typeof(recipientId) == 'number' ? recipientId: this.currentTab;
	BX.MessengerCommon.endSendWriting(recipientId);

	text = text.replace('    ', "\t");
	text = BX.util.trim(text);
	if (text.length == 0)
		return false;

	if (text.indexOf('/color') == 0)
	{
		var color = text.split(" ")[1];
		if (color && this.openChatFlag)
		{
			BX.MessengerCommon.setColor(color, recipientId.substr(4));
		}

		return false;
	}
	else if (text.indexOf('/rename') == 0)
	{
		var title = text.substr(7);
		if (title && this.openChatFlag)
		{
			BX.MessengerCommon.renameChat(recipientId.substr(4), title);
		}

		return false;
	}


	var chatId = recipientId.toString().substr(0,4) == 'chat'? recipientId.toString().substr(4): (this.userChat[recipientId]? this.userChat[recipientId]: 0);

	if (this.errorMessage[recipientId])
	{
		BX.MessengerCommon.sendMessageRetry();
		this.errorMessage[recipientId] = false;
	}

	var messageTmpIndex = this.messageTmpIndex;
	this.message['temp'+messageTmpIndex] = {
		'id': 'temp'+messageTmpIndex,
		'chatId': chatId,
		'senderId': this.BXIM.userId,
		'recipientId': recipientId,
		'date': new Date(),
		'textOriginal': text,
		'text': BX.MessengerCommon.prepareText(text, true, true, true)
	};
	if (!this.showMessage[recipientId])
		this.showMessage[recipientId] = [];
	this.showMessage[recipientId].push('temp'+messageTmpIndex);

	this.messageTmpIndex++;
	BX.localStorage.set('mti', this.messageTmpIndex, 5);
	if (recipientId != this.currentTab)
		return false;

	clearTimeout(this.textareaHistoryTimeout);

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

	this.textareaHistory[recipientId] = '';
	BX.MessengerCommon.sendMessageAjax(messageTmpIndex, recipientId, text, recipientId.toString().substr(0,4) == 'chat');

	return true;
};

BX.ImMessengerMobile.prototype.textareaIconPrepare = function()
{

}
BX.ImMessengerMobile.prototype.setUpdateStateStep = function()
{

}
BX.ImMessengerMobile.prototype.setUpdateStateStepCount = function()
{

}

BX.ImMessengerMobile.prototype.extendChat = function (dialogId, isChat, isLines)
{
	app.openTable({
		url: this.BXIM.pathToRoot + 'mobile/index.php?mobile_action=get_user_list&only_business='+(isLines? 'Y':'N'),
		callback: BX.delegate(function (data)
		{
			if (!(data && data.a_users && data.a_users[0]))
				return;

			var arUsers = [];
			for (var i = 0; i < data.a_users.length; i++)
				arUsers.push(data.a_users[i]['ID'].toString());

			var data = false;
			if (!isChat)
			{
				arUsers.push(dialogId);
				data = {'IM_CHAT_ADD': 'Y', 'USERS': JSON.stringify(arUsers), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()};
			}
			else
			{
				data = {'IM_CHAT_EXTEND': 'Y', 'CHAT_ID': dialogId.substr(4), 'USERS': JSON.stringify(arUsers), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()};
			}
			if (!data)
				return false;

			BX.ajax({
				url: this.BXIM.pathToRoot + 'mobile/ajax.php?mobile_action=im&' + (isChat ? 'CHAT_EXTEND' : 'CHAT_ADD'),
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: data,
				onsuccess: BX.delegate(function (data)
				{
					if (data.ERROR == '')
					{
						if (!isChat && data.CHAT_ID)
						{
							BXMobileApp.PageManager.loadPageUnique({
								'url' : this.BXIM.pathToRoot + 'mobile/im/dialog.php'+(!app.enableInVersion(11)? "?id=chat"+data.CHAT_ID: ""),
								'bx24ModernStyle' : true,
								'data': {dialogId: 'chat' + data.CHAT_ID}
							});
						}
					}
					else
					{
						app.alert({'text': data.ERROR});
					}
				}, this)
			});
		}, this),
		set_focus_to_search: true,
		markmode: true,
		multiple: true,
		return_full_mode: true,
		modal: true,
		alphabet_index: true,
		outsection: false,
		okname: BX.message('IM_M_EXTEND')
	});
}

BX.ImMessengerMobile.prototype.linesTransfer = function (chatId)
{
	app.openTable({
		url: this.BXIM.pathToRoot + 'mobile/index.php?mobile_action=get_user_list&only_business=Y',
		callback: BX.delegate(function (data)
		{
			if ( ! (data && data.a_users && data.a_users[0]) )
				return;

			var user = data.a_users[0];
			var user_id = user['ID'].toString();

			BX.ajax({
				url: this.BXIM.pathToAjax+'?LINES_TRANSFER&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'COMMAND': 'transfer', 'CHAT_ID' : chatId, 'TRANSFER_ID': user_id, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(){
					app.closeController();
				}, this)
			});
		}, this),
		set_focus_to_search: true,
		markmode: true,
		multiple: false,
		return_full_mode: true,
		modal: true,
		alphabet_index: true,
		outsection: false,
		okname: BX.message('IM_OL_INVITE_TRANSFER')
	});
}

BX.ImMessengerMobile.prototype.linesVoteHeadDialog = function(bindElement, sessionId, inline)
{
	inline = inline || false;

	var rating = bindElement.getAttribute('data-rating') || 0;

	var ratingNode = BX.MessengerCommon.linesVoteHeadNodes(sessionId, rating, true, inline? null: bindElement);

	if (inline)
		return ratingNode;

	return false;
}

BX.ImMessengerMobile.prototype.linesCreateLead = function()
{
	var chatId = this.currentTab.toString().substr(4);
	var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);
	if (session.crm == 'N')
	{
		BX.MessengerCommon.linesCreateLead(chatId);
	}
}
BX.ImMessengerMobile.prototype.linesMarkAsSpam = function()
{
	var chatId = this.currentTab.toString().substr(4);
	var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);

	BX.MessengerCommon.linesMarkAsSpam(chatId);
}
BX.ImMessengerMobile.prototype.linesCloseDialog = function()
{
	var chatId = this.currentTab.toString().substr(4);
	var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);

	BX.MessengerCommon.linesCloseDialog(chatId);
}
BX.ImMessengerMobile.prototype.linesTogglePinMode = function()
{
	var chatId = this.currentTab.toString().substr(4);
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

	this.dialogStatusRedraw();

	BX.MessengerCommon.linesActivatePinMode(chatId, flag);
}

BX.ImMessengerMobile.prototype.linesToggleSilentMode = function()
{
	var chatId = this.currentTab.toString().substr(4);
	var flag;

	if (this.linesSilentMode[chatId])
	{
		flag = 'N';
	}
	else
	{
		flag = 'Y';
	}
	this.linesSilentMode[chatId] = flag == 'Y';

	this.dialogStatusRedraw()

	//BX.MessengerCommon.linesActivateSilentMode(chatId, flag);
}

BX.ImMessengerMobile.prototype.updateMessageCount = function(send)
{
}

BX.ImMessengerMobile.prototype.messageReply = function(userId)
{
	if (!this.users[userId] || this.users[userId].fake)
		return false;

	var userName =  BX.util.htmlspecialcharsback(this.users[userId].name);
	userName = '[USER='+userId+']'+userName+'[/USER] ';

	if (!this.textareaHistory[this.currentTab])
		this.textareaHistory[this.currentTab] = '';

	this.textareaHistory[this.currentTab] = this.textareaHistory[this.currentTab]+' '+userName;
	BXMobileApp.UI.Page.TextPanel.setText(this.textareaHistory[this.currentTab]);
	BXMobileApp.UI.Page.TextPanel.focus();
}

BX.ImMessengerMobile.prototype.openMessageMenu = function(messageId)
{
	var isKeyboardShown = (window.app.enableInVersion(14) && window.platform == "ios")
		? window.BXMobileAppContext.isKeyboardShown()
		: this.BXIM.keyboardShow ;

	if (!this.message[messageId] || isKeyboardShown || BX.localStorage.get('impmh'))
		return false;

	if (this.chat[this.message[messageId].chatId] && !BX.MessengerCommon.userInChat(this.message[messageId].chatId))
	{
		return false;
	}

	var sheetButtons = [];

	if (!(this.chat[this.message[messageId].chatId] && this.chat[this.message[messageId].chatId].type == 'call'))
	{
		var iLikeThis = BX.MessengerCommon.messageIsLike(messageId);
		sheetButtons.push({
			title: BX.message(iLikeThis? "IM_MENU_MESS_DISLIKE": "IM_MENU_MESS_LIKE"),
			callback: BX.delegate(function () { BX.MessengerCommon.messageLike(messageId); }, this)
		});
	}

	var userId = this.message[messageId].senderId;
	if (userId > 0)
	{
		if (this.generalChatId == this.message[messageId].chatId && !this.canSendMessageGeneralChat)
		{
		}
		else
		{
			sheetButtons.push({
				title: BX.message("IM_MENU_MESS_REPLY"),
				callback: BX.delegate(function () { this.messageReply(userId); }, this)
			});
		}
	}

	/*
	sheetButtons.push({
		title: BX.message("IM_MENU_MESS_QUOTE"),
		callback: BX.delegate(function () {}, this)
	});
	*/
	/*
	sheetButtons.push({
		title: BX.message("IM_MENU_MESS_LIKE_LIST_2"),
		callback: BX.delegate(function () {}, this)
	});
	*/

	var deleteMessageId = 0;
	var firstMessageId = BX('im-message-'+messageId)
	if (firstMessageId)
	{
		var nodes = BX.findChildrenByClassName(firstMessageId.parentNode.parentNode, "bx-messenger-message");
		for (var i = nodes.length - 1; i >= 0 && deleteMessageId == 0; i--)
		{
			if (!BX.hasClass(nodes[i], 'bx-messenger-message-deleted'))
			{
				deleteMessageId = nodes[i].id.substr(11);
			}
		}
	}

	if (BX.MessengerCommon.checkEditMessage(deleteMessageId, 'edit'))
	{
		if (app.enableInVersion(14))
		{
			sheetButtons.push({
				title: BX.message("IM_MENU_MESS_EDIT"),
				callback: BX.delegate(function () { this.editMessage(deleteMessageId); }, this)
			});
		}
	}

	if (!this.users[this.BXIM.userId].extranet)
	{
		sheetButtons.push({
			title: BX.message("IM_MENU_TO_TASK"),
			callback: BX.delegate(function () { BX.MessengerCommon.shareMessageAjax(messageId, 'TASK') }, this)
		});

		if (this.message[messageId].params && this.message[messageId].params.DATE_TS && this.message[messageId].params.DATE_TS.length > 0)
		{
			sheetButtons.push({
				title: BX.message("IM_MENU_TO_CALEND"),
				callback: BX.delegate(function () { BX.MessengerCommon.shareMessageAjax(messageId, 'CALEND') }, this)
			});
		}

		sheetButtons.push({
			title: BX.message("IM_MENU_TO_CHAT"),
			callback: BX.delegate(function () { BX.MessengerCommon.shareMessageAjax(messageId, 'CHAT') }, this)
		});

		sheetButtons.push({
			title: BX.message("IM_MENU_TO_POST_2"),
			callback: BX.delegate(function () { BX.MessengerCommon.shareMessageAjax(messageId, 'POST') }, this)
		});
	}

	if (BX.MessengerCommon.checkEditMessage(deleteMessageId, 'delete'))
	{
		sheetButtons.push({
			title: BX.message("IM_MENU_MESS_DEL"),
			callback: BX.delegate(function () { this.deleteMessage(deleteMessageId); }, this)
		});
	}

	if (sheetButtons.length > 0)
	{
		(new BXMobileApp.UI.ActionSheet({buttons: sheetButtons},"im-message-menu")).show();
	}
}

BX.ImMessengerMobile.prototype.editMessage = function(messageId, check)
{
	if (!BX.MessengerCommon.checkEditMessage(messageId, 'edit'))
		return false;

	var formSettings = {
		mentionButton: {
			dataSource: {
				return_full_mode: "YES",
				outsection: "NO",
				multiple: "NO",
				alphabet_index: "YES",
				url: BX.message('MobileSiteDir') + 'mobile/index.php?mobile_action=get_user_list'
			}
		},
		smileButton: {},
		message : {
			text : this.message[messageId].textOriginal
		},
		okButton: {
			callback : function(data){
				BX.MessengerCommon.editMessageAjax(messageId, data.text)
			},
			name: BX.message('IM_MENU_SAVE')
		},
		cancelButton : {
			callback : BX.delegate(function(){
				this.editMessageCancel();
			}, this),
			name : BX.message('IM_MENU_CANCEL')
		}
	};

	app.exec('showPostForm', formSettings);
};

BX.ImMessengerMobile.prototype.editMessageCancel = function()
{
	this.keyboardShow = false;
}

BX.ImMessengerMobile.prototype.deleteMessage = function(messageId, check)
{
	if (!BX.MessengerCommon.checkEditMessage(messageId, 'delete'))
		return false;

	if (check !== false)
	{
		var message = this.message[messageId].text.length > 50? this.message[messageId].text.substr(0, 47) + '...': this.message[messageId].text;

		app.confirm({
			title : BX.message('IM_MENU_MESS_DEL_CONFIRM'),
			text : message?'"' + message + '"': '',
			buttons : [BX.message('IM_MENU_MESS_DEL_YES'), BX.message('IM_MENU_MESS_DEL_NO')],
			callback : function (btnNum)
			{
				if (btnNum == 1)
				{
					BX.MessengerCommon.deleteMessageAjax(messageId);
				}
			}
		});
	}
	else
	{
		this.deleteMessageAjax(messageId);
	}
}

})();

(function() {

if (BX.ImNotifyMobile)
	return;

BX.ImNotifyMobile = function(rootObject, params)
{
	this.BXIM = rootObject;
	this.sendAjaxTry = 0;
	this.notifyLastId = 0;
	this.notifyLoadFlag = false;
	this.counters = {};
	this.timeoutNotifyViewedWait = null;
}

BX.ImNotifyMobile.prototype.updateNotifyCounters = function (arCounter)
{
	if (typeof(arCounter) == "object")
	{
		for (var i in arCounter)
			this.counters[i] = arCounter[i];
	}
}

BX.ImNotifyMobile.prototype.notifyViewedWait = function (params)
{
	clearTimeout(this.timeoutNotifyViewedWait);
	if (!this.notifyLoadFlag)
	{
		this.timeoutNotifyViewedWait = setTimeout(BX.delegate(function(){
			this.notifyViewed(params.lastId)
		}, this), 300);

		this.BXIM.notifyCount = 0;
		this.BXIM.updateCounter();
	}
	else
	{
		clearTimeout(this.timeoutNotifyViewedWait);
		this.timeoutNotifyViewedWait = setTimeout(BX.delegate(function(){
			this.notifyViewedWait(params);
		}, this), 2000)
	}
}

BX.ImNotifyMobile.prototype.notifyViewed = function (id)
{
	BX.ajax({
		url: this.BXIM.pathToAjax,
		method: 'POST',
		dataType: 'json',
		skipAuthCheck: true,
		data: {'IM_NOTIFY_READ': 'Y', 'ID': id, 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function (data)
		{
			if (data.ERROR.length == 0)
			{
				this.sendAjaxTry = 0;
				this.notifyLastId = 0;
			}
		}, this),
	});

	return true;
}

})();

(function() {

if (BX.ImDiskManagerMobile)
	return;

BX.ImDiskManagerMobile = function(rootObject, params)
{
	this.BXIM = rootObject;
	this.notify = params.notifyClass;
	this.enable = params.enable;
	this.enableExternal = params.enableExternal;
	this.lightVersion = false;

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
	this.messageBlock = {};

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
				timeout: 30,
				async: false,
				data: {'IM_FILE_UNREGISTER' : 'Y', CHAT_ID: chatId, FILES: JSON.stringify(this.filesProgress), MESSAGES: JSON.stringify(messages), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}
	}, this);
}

BX.ImDiskManagerMobile.prototype.getChatId = function()
{
	var isChat = this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat';
	if (isChat)
	{
		return this.BXIM.messenger.currentTab.toString().substr(4);
	}

	var chatId = this.BXIM.messenger.userChat[this.BXIM.messenger.currentTab];
	if (chatId)
	{
		return chatId;
	}

	return 0;
};

BX.ImDiskManagerMobile.prototype.chatDialogInit = function()
{
	this.formAgents['imDialog'] = BX.Uploader.getInstance({
		id : 'imDialog',
		allowUpload : "A",
		uploadMethod : "deferred",
		uploadFormData : "Y",
		showImage : true,
		filesInputMultiple: true,
		uploadFileUrl : this.BXIM.pathToFileAjax,
		input : null,
		fields: {preview: {params: {width: 500, height: 500}}}
	});
	this.formAgents['imDialog'].form = this.messenger.popupMessengerFileForm;

	BX.addCustomEvent(this.formAgents['imDialog'], "onError", BX.delegate(BX.MessengerCommon.diskChatDialogUploadError, BX.MessengerCommon));

	BX.addCustomEvent(this.formAgents['imDialog'], "onFileIsInited", BX.delegate(function(id, file, agent){
		BX.MessengerCommon.diskChatDialogFileInited(id, file, agent);
		BX.addCustomEvent(file, 'onUploadStart', BX.delegate(BX.MessengerCommon.diskChatDialogFileStart, BX.MessengerCommon));
		BX.addCustomEvent(file, 'onUploadProgress', BX.delegate(BX.MessengerCommon.diskChatDialogFileProgress, BX.MessengerCommon));
		BX.addCustomEvent(file, 'onUploadDone', BX.delegate(BX.MessengerCommon.diskChatDialogFileDone, BX.MessengerCommon));
		BX.addCustomEvent(file, 'onUploadError', BX.delegate(BX.MessengerCommon.diskChatDialogFileError, BX.MessengerCommon));
	}, this));
};

BX.ImDiskManagerMobile.prototype.uploadFromMobile = function(image, text)
{
	var dataBlob = BX.UploaderUtils.dataURLToBlob("data:image/jpg;base64,"+image);
	dataBlob.name = 'mobile_'+BX.Main.Date.format("Ymd_His")+'.jpg';
	this.formAgents['imDialog'].messageText = text? text: '';
	this.formAgents['imDialog'].onChange([dataBlob]);
};

BX.ImDiskManagerMobile.prototype.uploadFromDisk = function(selected, text)
{
	text = text || '';
	var chatId = this.messenger.popupMessengerFileFormChatId.value;
	if (!this.files[chatId])
		this.files[chatId] = {};

	var paramsFileId = [];
	for(var fileId in selected)
	{
		this.files[chatId]['disk'+fileId] = {
			'id': 'disk'+fileId,
			'templateId': 'disk'+fileId,
			'chatId': chatId,
			'date': new Date(selected[fileId].modifyDateInt*1000),
			'type': 'file',
			'preview': '',
			'name': selected[fileId].name,
			'size': selected[fileId].sizeInt,
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
		'text': BX.MessengerCommon.prepareText(text, true, true, true),
		'textOriginal': text,
		'params': {'FILE_ID': paramsFileId, 'CLASS': olSilentMode == "Y"? "bx-messenger-content-item-system": ""}
	};
	if (!this.messenger.showMessage[recipientId])
		this.messenger.showMessage[recipientId] = [];

	this.messenger.showMessage[recipientId].push(tmpMessageId);
	BX.MessengerCommon.drawMessage(recipientId, this.messenger.message[tmpMessageId]);
	BX.MessengerCommon.drawProgessMessage(tmpMessageId);

	this.messenger.sendMessageFlag++;

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

BX.ImDiskManagerMobile.prototype.diskChatDialogFileInited = function(id, file, agent)
{
	var chatId = agent.form.CHAT_ID.value;

	if (!this.files[chatId])
		this.files[chatId] = {};

	this.files[chatId][id] = {
		'id': id,
		'templateId': id,
		'chatId': chatId,
		'date': new Date(),
		'type': file.isImage? 'image': 'file',
		'preview': file.isImage? file.canvas: '',
		'name': file.name,
		'size': file.file.size,
		'status': 'upload',
		'progress': -1,
		'authorId': this.BXIM.userId,
		'authorName': this.messenger.users[this.BXIM.userId].name,
		'urlPreview': '',
		'urlShow': '',
		'urlDownload': ''
	};

	if (!this.filesRegister[chatId])
		this.filesRegister[chatId] = {};

	this.filesRegister[chatId][id] = {
		'id': id,
		'type': this.files[chatId][id].type,
		'mimeType': file.file.type,
		'name': this.files[chatId][id].name,
		'size': this.files[chatId][id].size
	};

	BX.MessengerCommon.diskChatDialogFileRegister(chatId);

}
BX.ImDiskManagerMobile.prototype.saveToDisk = function()
{
	return true
}
BX.ImDiskManagerMobile.prototype.delete = function()
{
	return true
}
})();

(function() {

if (BX.ImWebRTCMobile)
	return;

BX.ImWebRTCMobile = function(rootObject, params)
{
	this.BXIM = rootObject;

	this.messenger = this.BXIM.messenger;
	this.desktop = this.BXIM.desktop;

	this.callMethod = params.callMethod;
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
	this.phoneNumberUser = '';
	this.phoneParams = {};
	this.phoneAPI = null;
	this.phoneDisconnectAfterCallFlag = false;
	this.phoneCurrentCall = null;
	this.mobileSupport = params.mobileSupport;
	this.phoneCrm = params.phoneCrm? params.phoneCrm: {};
	this.phoneSpeakerEnable = false;
	this.phoneMicMuted = false;
	this.phoneHolded = false;
	this.phoneRinging = 0;
	this.phoneTransferEnabled = false;
	this.phoneConnectedInterval = null;
	this.phoneDeviceDelayTimeout = null;
	this.callNotify = null;

	this.debug = false;
	this.audioMuted = false;

	this.initiator = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callInit = false;
	this.callInitUserId = 0;
	this.callActive = false;

	this.turnServer = params.turnServer;
	this.turnServerFirefox = params.turnServerFirefox;
	this.turnServerLogin = params.turnServerLogin;
	this.turnServerPassword = params.turnServerPassword;

	this.phoneEnabled = params.phoneEnabled;
	this.phoneDeviceActive = params.phoneDeviceActive == 'Y';
	this.phoneCallerID = '';
	this.phoneLogin = "";
	this.phoneServer = "";
	this.phoneCheckBalance = false;
	this.phoneCallHistory = {};

	this.callOverlayOptions = {};

	this.debug = true; // TODO change to false

	if (this.phoneSupport() && this.BXIM.mobileAction == 'RECENT')
	{
		BX.MobileCallUI.init();
		this.pullPhoneUiEvent();

		BX.MessengerCommon.pullPhoneEvent();

		var notificationHandler = BX.delegate(function (push)
		{
			var pushParams = BXMobileApp.PushManager.prepareParams(push);
			if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) == 'VI_CALL_')
			{
				BX.onCustomEvent(window, "onPull-voximplant", [{command : "invite", params : pushParams.PARAMS}]);
			}
		}, this);

		if(app.enableInVersion(15))
		{
			BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function()
			{

				var push = BXMobileApp.PushManager.getLastNotification();
				if(push && push != {})
					notificationHandler(push);
			});

			notificationHandler(BXMobileApp.PushManager.getLastNotification());
		}
		else
		{
			BX.addCustomEvent("onOpenPush", notificationHandler);
		}


		var count = 0;
		this.viIntiveInterval = setInterval(BX.delegate(function(){
			var viInvite = BX.localStorage.get('viInvite');
			if (viInvite)
			{
				BX.onCustomEvent(window, "onPull-voximplant", [{command : "invite", params : viInvite}]);
				BX.localStorage.remove('viInvite');
				clearInterval(this.viIntiveInterval);
			}
			if (count == 30)
			{
				clearInterval(this.viIntiveInterval);
			}
		}, this), 1000)
	}
};

BX.ImWebRTCMobile.prototype.setCallMethod = function(type)
{
	if (type == 'telephony')
	{
		this.callMethod = type;
	}
	else if (type == 'combined')
	{
		this.callMethod = type;
	}
	else
	{
		this.callMethod = 'device';
	}
}

BX.ImWebRTCMobile.prototype.pullPhoneUiEvent = function()
{
	BX.MobileCallUI.setListener(BX.delegate(function(eventName, eventParams)
	{
		/* buttons */
		if(eventName == BX.MobileCallUI.events.onHangup)
		{
			BX.MobileCallUI.form.cancelDelayedClosing();

			this.phoneCallFinish();
			this.callAbort();
			this.callOverlayClose();
		}
		else if(eventName == BX.MobileCallUI.events.onSpeakerphoneChanged)
		{
			this.phoneToggleSpeaker(eventParams.selected);
		}
		else if(eventName == BX.MobileCallUI.events.onMuteChanged)
		{
			this.phoneToggleAudio(eventParams.selected)
		}
		else if(eventName == BX.MobileCallUI.events.onPauseChanged)
		{
			BX.MessengerCommon.phoneToggleHold(eventParams.selected)
		}
		else if(eventName == BX.MobileCallUI.events.onCloseClicked)
		{
			this.phoneCallFinish();
			this.callAbort();
		}
		else if(eventName == BX.MobileCallUI.events.onAnswerClicked)
		{
			this.BXIM.stopRepeatSound('ringtone');
			this.phoneIncomingAnswer();
		}
		else if(eventName == BX.MobileCallUI.events.onSkipClicked)
		{
			this.phoneCallFinish();
			this.callAbort();
			this.callOverlayClose();
		}
		else if(eventName == BX.MobileCallUI.events.onAnswerClicked)
		{
		}
		/* NumPad */
		else if(eventName == BX.MobileCallUI.events.onNumpadButtonClicked)
		{
			BX.MessengerCommon.phoneSendDTMF(eventParams);
		}
		else if(eventName == BX.MobileCallUI.events.onPhoneNumberReceived)
		{
			this.phoneCall(eventParams);
		}
		else if(eventName.substr(0, 4) == "crm_")
		{
			eventName = eventName.substr(4).split("_");

			var crmUrl = '';
			if (eventName[0] == "deal")
			{
				crmUrl = this.BXIM.pathToCrmDeal.replace('#ID#', eventName[1]);
			}
			else if (eventName[0] == "company")
			{
				crmUrl = this.BXIM.pathToCrmCompany.replace('#ID#', eventName[1]);
			}
			else if (eventName[0] == "contact")
			{
				crmUrl = this.BXIM.pathToCrmContact.replace('#ID#', eventName[1]);
			}
			else if (eventName[0] == "lead")
			{
				crmUrl = this.BXIM.pathToCrmLead.replace('#ID#', eventName[1]);
			}

			BXMobileApp.PageManager.loadPageBlank({
				'url' : crmUrl,
				'bx24ModernStyle' : true
			});
			BX.MobileCallUI.form.rollUp();
		}
		/* Contact list */
		else if(eventName == BX.MobileCallUI.events.onContactListChoose)
		{
			//eventParams - contact item
		}
		else if(eventName == BX.MobileCallUI.events.onContactListMenuChoose)
		{
			//eventParams - menu item
		}
		/* Context menu form */
		else if(eventName == BX.MobileCallUI.events.onContactListMenuChoose)
		{
			//eventParams - menu item
		}
	}, this));
}

BX.ImWebRTCMobile.prototype.phoneCall = function(number, params)
{
	if (!this.phoneSupport())
		return false;

	if (this.debug)
		this.phoneLog(number, params);

	this.phoneNumberUser = BX.util.htmlspecialchars(number);

	numberOriginal = number;
	number = BX.MessengerCommon.phoneCorrect(number);
	if (typeof(params) != 'object')
		params = {};

	if (number.length <= 0)
	{
		this.BXIM.openConfirm({title: BX.message('IM_PHONE_WRONG_NUMBER'), message: BX.message('IM_PHONE_WRONG_NUMBER_DESC')});
		return false;
	}

	BX.MobileCallUI.numpad.close();

	if (this.callActive || this.callInit)
		return false;

	this.BXIM.playSound("start");

	this.initiator = true;
	this.callInitUserId = this.BXIM.userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.phoneNumber = number;
	this.phoneParams = params;

	this.callOverlayShow({
		toUserId : 0,
		phoneNumber : this.phoneNumber,
		callTitle : this.phoneNumberUser,
		fromUserId : this.BXIM.userId,
		status : BX.message('IM_M_CALL_ST_CONNECT'),
		state : BX.MobileCallUI.form.state.OUTGOING
	});

	if (!this.phoneLogin || !this.phoneServer)
	{
		BX.MessengerCommon.phoneAuthorize();
	}
	else
	{
		this.phoneApiInit();
	}
}

BX.ImWebRTCMobile.prototype.phoneOnIncomingCall = function(e)
{
	BX.MessengerCommon.phoneOnIncomingCall(e);
}

BX.ImWebRTCMobile.prototype.phoneIncomingWait = function(params)
{
	/*chatId, callId, callerId, companyPhoneNumber, isCallback*/
	params.isCallback = !!params.isCallback;
	if (this.debug)
		this.phoneLog('incoming call', JSON.stringify(params));

	this.phoneNumberUser = BX.util.htmlspecialchars(params.callerId);
	params.callerId = params.callerId.replace(/[^a-zA-Z0-9\.]/g, '');

	if (!this.callActive && !this.callInit)
	{
		this.initiator = true;
		this.callInitUserId = 0;
		this.callInit = true;
		this.callActive = false;
		this.callUserId = 0;
		this.callChatId = 0;
		this.phoneIncoming = true;
		this.phoneCallTime = 0;
		this.phoneCallId = params.callId;
		this.phoneNumber = params.callerId;
		this.phoneParams = {};

		this.callOverlayShow({
			toUserId : this.BXIM.userId,
			phoneNumber : this.phoneNumber,
			companyPhoneNumber : params.companyPhoneNumber,
			callTitle : this.phoneNumberUser,
			fromUserId : 0,
			isCallback : params.isCallback,
			status : (params.isCallback ? BX.message('IM_PHONE_INVITE_CALLBACK') : BX.message('IM_PHONE_INVITE')),
			state : BX.MobileCallUI.form.state.INCOMING
		});

		this.callOverlayDrawCrm();
	}
}

BX.ImWebRTCMobile.prototype.phoneIncomingAnswer = function()
{
	this.callOverlayState(BX.MobileCallUI.form.state.WAITING);

	this.callSelfDisabled = true;
	BX.MessengerCommon.phoneCommand((this.phoneTransferEnabled? 'answerTransfer': 'answer'), {'CALL_ID' : this.phoneCallId});

	BX.MobileCallUI.numpad.close();

	if (!this.phoneLogin || !this.phoneServer)
	{
		BX.MessengerCommon.phoneAuthorize();
	}
	else
	{
		this.phoneApiInit();
	}
}

BX.ImWebRTCMobile.prototype.phoneApiInit = function()
{
	if (!this.phoneSupport())
		return false;

	if (!this.phoneLogin || !this.phoneServer)
	{
		this.phoneCallFinish();
		this.callOverlayProgress('offline');
		this.callAbort(BX.message('IM_PHONE_ERROR'));

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

	this.phoneAPI = BX.MobileVoximplant.getInstance();
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.SDKReady, BX.delegate(this.phoneOnSDKReady, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.ConnectionEstablished, BX.delegate(this.phoneOnConnectionEstablished, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.ConnectionFailed, BX.delegate(this.phoneOnConnectionFailed, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.ConnectionClosed, BX.delegate(this.phoneOnConnectionClosed, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.IncomingCall, BX.delegate(this.phoneOnIncomingCall, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.AuthResult, BX.delegate(this.phoneOnAuthResult, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.MicAccessResult, BX.delegate(this.phoneOnMicResult, this));
	this.phoneAPI.addEventListener(BX.MobileVoximplant.events.NetStatsReceived, BX.delegate(this.phoneOnNetStatsReceived, this));
	this.phoneAPI.init();

	return true;
}

BX.ImWebRTCMobile.prototype.phoneOnSDKReady = function()
{
	this.phoneLog('SDK ready');

	if (!this.phoneAPI.connected())
	{
		this.callOverlayProgress('wait');
		this.callOverlayStatus(BX.message('IM_M_CALL_ST_WAIT_ACCESS'));
		this.phoneAPI.connect();
	}
	else
	{
		this.phoneLog('Connection exists');

		this.callOverlayProgress('connect');
		this.callOverlayStatus(BX.message('IM_M_CALL_ST_CONNECT'));
		this.phoneOnAuthResult({result: true});
	}
}

BX.ImWebRTCMobile.prototype.phoneOnConnectionEstablished = function(e)
{
	BX.MessengerCommon.phoneOnConnectionEstablished(e);
	this.phoneAPI.login();
}

BX.ImWebRTCMobile.prototype.phoneOnConnectionFailed = function(e)
{
	BX.MessengerCommon.phoneOnConnectionFailed(e);
}

BX.ImWebRTCMobile.prototype.phoneOnConnectionClosed = function(e)
{
	BX.MessengerCommon.phoneOnConnectionClosed(e);
}

BX.ImWebRTCMobile.prototype.phoneOnMicResult = function(e)
{
	BX.MessengerCommon.phoneOnMicResult(e);
}

BX.ImWebRTCMobile.prototype.phoneOnAuthResult = function(e)
{
	BX.MessengerCommon.phoneOnAuthResult(e);
}

BX.ImWebRTCMobile.prototype.phoneOnNetStatsReceived = function(e)
{
	BX.MessengerCommon.phoneOnNetStatsReceived(e);
}

BX.ImWebRTCMobile.prototype.phoneOnCallConnected = function(e)
{
	this.phoneLog('Call connected', e);

	this.callOverlayProgress('online');
	this.callOverlayStatus(BX.message('IM_M_CALL_ST_ONLINE'));
	this.callActive = true;
}

BX.ImWebRTCMobile.prototype.phoneOnCallDisconnected = function(e)
{
	BX.MessengerCommon.phoneOnCallDisconnected(e);
}

BX.ImWebRTCMobile.prototype.phoneOnCallFailed = function(e)
{
	BX.MessengerCommon.phoneOnCallFailed(e);
}

BX.ImWebRTCMobile.prototype.phoneOnProgressToneStart = function(e)
{
	BX.MessengerCommon.phoneOnProgressToneStart(e);
}

BX.ImWebRTCMobile.prototype.phoneOnProgressToneStop = function(e)
{
	BX.MessengerCommon.phoneOnProgressToneStop();
}

BX.ImWebRTCMobile.prototype.callPhoneOverlayMeter = function(percent)
{}

BX.ImWebRTCMobile.prototype.callOverlayProgress = function(progress)
{
	this.phoneLog('set progress: ', progress)

	if (progress == this.callOverlayOptions.progress)
		return false;

	this.callOverlayOptions.progress = progress;

	if (progress == 'connect')
	{
	}
	else if (progress == 'wait')
	{
		this.callOverlayState(BX.MobileCallUI.form.state.WAITING);
	}
	else if (progress == 'online')
	{
		if (!this.phonePortalCall)
		{
			var headerLabels = {};
			if (this.phoneCallConfig.RECORDING == "Y")
			{
				headerLabels.thirdSmallHeader = {'text' : BX.message('IM_PHONE_REC_NOW'), textColor : "#7fc62c"};
			}
			else
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
			}

			BX.MobileCallUI.form.updateHeader(headerLabels);
		}

		this.callOverlayState(BX.MobileCallUI.form.state.STARTED);
	}
	else if (progress == 'offline' || progress == 'error')
	{
		if (progress == 'offline')
		{
			if (!this.phonePortalCall)
			{
				var headerLabels = {};
				if (this.phoneCallConfig.RECORDING == "Y" && this.phoneCallTime > 0)
				{
					headerLabels.thirdSmallHeader = {'text' : BX.message('IM_PHONE_REC_DONE'), textColor : "#7fc62c"};
				}
				else
				{
					headerLabels.thirdSmallHeader = {'text' : ''};
				}
				BX.MobileCallUI.form.updateHeader(headerLabels);

				var footerLabels = {};
				if (this.phoneCrm.LEAD_DATA && !this.phoneCrm.CONTACT_DATA && !this.phoneCrm.COMPANY_DATA && this.phoneCallConfig.CRM_CREATE == 'lead')
				{
					footerLabels.actionDoneHint = {'text': BX.message('IM_PHONE_LEAD_SAVED')};
				}
				else
				{
					footerLabels.actionDoneHint = {'text': ''};
				}
				BX.MobileCallUI.form.updateFooter(footerLabels);
			}
		}
		else
		{
			var headerLabels = {};
			headerLabels.thirdSmallHeader = {'text': ''};
			BX.MobileCallUI.form.updateHeader(headerLabels);

			var footerLabels = {};
			footerLabels.actionDoneHint = {'text': ''};
			BX.MobileCallUI.form.updateFooter(footerLabels);
		}

		this.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
		BX.MobileCallUI.form.expand();
		BX.MobileCallUI.numpad.close();
	}
}

BX.ImWebRTCMobile.prototype.callOverlayStatus = function(status)
{
	if (!status || this.callOverlayOptions.status == status)
		return false;

	this.phoneLog('callOverlayStatus', status);
	this.callOverlayOptions.status = status;

	BX.MobileCallUI.form.updateFooter({callStateLabel: {text: status}})
}

BX.ImWebRTCMobile.prototype.callOverlayDoneHint = function(hint)
{
	if (!hint || this.callOverlayOptions.hint == hint)
		return false;

	this.phoneLog('callOverlayDoneHint', hint);
	this.callOverlayOptions.hint = hint;

	BX.MobileCallUI.form.updateFooter({actionDoneHint: {text: hint}})
}

BX.ImWebRTCMobile.prototype.callOverlayState = function(state)
{
	if (!state || this.callOverlayOptions.state == state)
		return false;

	this.phoneLog('callOverlayState', state);
	this.callOverlayOptions.state = state;

	BX.MobileCallUI.form.updateFooter({}, state)
}

BX.ImWebRTCMobile.prototype.callOverlayUpdatePhoto = function()
{
}

BX.ImWebRTCMobile.prototype.callOverlayShow = function(params)
{
	BX.MobileCallUI.numpad.close();

	var callIncoming = params.toUserId == this.BXIM.userId;
	var callUserId = callIncoming? params.fromUserId: params.toUserId;
	this.callToPhone = true;

	var phoneNumber = '';
	if (params.phoneNumber == 'hidden')
	{
		phoneNumber = BX.message('IM_PHONE_HIDDEN_NUMBER');
	}
	else
	{
		if (params.callTitle)
		{
			phoneNumber = params.callTitle.toString();
		}
		else
		{
			phoneNumber = params.phoneNumber.toString();
		}

		if (phoneNumber.substr(0,1) == '8' || phoneNumber.substr(0,1) == '+')
		{
		}
		else if (!isNaN(parseInt(phoneNumber)) && phoneNumber.length >= 10)
		{
			phoneNumber = '+'+phoneNumber;
		}
	}

	var companyPhoneTitle = '';
	if (callIncoming)
	{
		companyPhoneTitle = params.companyPhoneNumber? BX.message('IM_PHONE_CALL_TO_PHONE').replace('#PHONE#', params.companyPhoneNumber): BX.message('IM_VI_CALL');
	}
	else
	{
		companyPhoneTitle = BX.message('IM_PHONE_OUTGOING');
	}
	this.callOverlayUserId = callUserId;

	BX.MessengerCommon.getUserParam(this.messenger.currentTab);
	BX.MessengerCommon.getUserParam(this.BXIM.userId);

	this.messenger.openChatFlag = this.messenger.currentTab.toString().substr(0,4) == 'chat';

	BX.MobileCallUI.form.show({
		headerLabels: {
			firstHeader: {text: phoneNumber},
			firstSmallHeader: {text: companyPhoneTitle, textColor: "#999999"}
		},
		footerLabels: {},
		middleLabels: {
			imageStub: {backgroundColor: '#464f58', display: 'visible'}
		},
		middleButtons: {}
	});

	if (params.status)
	{
		this.callOverlayStatus(params.status);
	}
	if (params.state)
	{
		this.callOverlayState(params.state);
	}
}

BX.ImWebRTCMobile.prototype.callOverlayTimer = function(state)
{
	state = typeof(state) == 'undefined'? 'start': state;
	if (this.callOverlayOptions.timerState == state)
		return false;

	this.phoneLog('callOverlayTimer', state);
	this.callOverlayOptions.timerState = state;

	if (state == 'start')
	{
		this.phoneCallTimeInterval = setInterval(BX.delegate(function(){
			this.phoneCallTime++;
		}, this), 1000);

		BX.MobileCallUI.form.startTimer();
	}
	else if (state == 'pause')
	{
		clearInterval(this.phoneCallTimeInterval);
		BX.MobileCallUI.form.pauseTimer();
	}
	else
	{
		clearInterval(this.phoneCallTimeInterval);
		BX.MobileCallUI.form.stopTimer();
	}
}

BX.ImWebRTCMobile.prototype.callOverlayDrawCrm = function()
{
	if (!this.phoneCrm.FOUND)
		return false;

	if (this.phoneCrm.FOUND == 'Y')
	{
		//console.log('CRM FOUND', this.phoneCrm);

		var crmContactName = this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.NAME? this.phoneCrm.CONTACT.NAME: '';
		var crmContactPhoto = this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.PHOTO? this.phoneCrm.CONTACT.PHOTO: '';
		var crmContactPost = this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.POST? this.phoneCrm.CONTACT.POST: '';
		var crmCompanyName = this.phoneCrm.COMPANY? this.phoneCrm.COMPANY: '';

		var headerLabels = {};
		if (!this.phonePortalCall)
		{
			if (this.phoneCallConfig.RECORDING == "Y")
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_ON'), textColor: "#ecd748"};
			}
			else
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
			}
		}

		if (crmContactName || crmContactPost || crmCompanyName)
		{
			if (crmContactName)
			{
				headerLabels.firstHeader = {'text': crmContactName};
			}
			if (crmContactPost)
			{
				headerLabels.firstSmallHeader = {'text': crmContactPost};
			}
			if (crmCompanyName)
			{
				headerLabels.secondSmallHeader = {'text': crmCompanyName};
			}
		}
		BX.MobileCallUI.form.updateHeader(headerLabels);

		if (crmContactPhoto)
		{
			BX.MobileCallUI.form.updateHeader({}, (crmContactPhoto.substr(0,4) != 'http'? location.origin: '')+crmContactPhoto);
		}

		var middleButtons = {};
		var middleChange = false;
		if (this.phoneCrm.DEALS && this.phoneCrm.DEALS.length > 0)
		{
			var middleLabels = {
				infoTitle: {
					text: ""
				},
				infoDesc: {
					text: this.phoneCrm.DEALS[0].TITLE
				},
				infoHeader: {
					text: this.phoneCrm.DEALS[0].STAGE,
					textColor: this.phoneCrm.DEALS[0].STAGE_COLOR
				},
				infoSum: {
					text: this.phoneCrm.DEALS[0].OPPORTUNITY
				}
			};

			if (this.phoneCrm.DEAL_URL)
			{
				middleButtons['button1'] = {
					text: BX.message('IM_PHONE_ACTION_T_DEAL'),
					sort: 100,
					eventName: "crm_deal_"+this.phoneCrm.DEALS[0].ID
				};
			}
			middleChange = true;
		}

		var dataSelect = [];
		if (this.phoneCrm.COMPANY_DATA && this.phoneCrm.CONTACT_DATA)
		{
			dataSelect = ['CONTACT_DATA', 'COMPANY_DATA', 'LEAD_DATA'];
		}
		else if (this.phoneCrm.CONTACT_DATA && this.phoneCrm.LEAD_DATA)
		{
			dataSelect = ['CONTACT_DATA', 'LEAD_DATA'];
		}
		else if (this.phoneCrm.LEAD_DATA && this.phoneCrm.COMPANY_DATA)
		{
			dataSelect = ['LEAD_DATA', 'COMPANY_DATA'];
		}
		else
		{
			if (this.phoneCrm.CONTACT_DATA)
			{
				dataSelect = ['CONTACT_DATA'];
			}
			else if (this.phoneCrm.COMPANY_DATA)
			{
				dataSelect = ['COMPANY_DATA'];
			}
			else if (this.phoneCrm.LEAD_DATA)
			{
				dataSelect = ['LEAD_DATA'];
			}
		}

		for (var i = 0; i < dataSelect.length; i++)
		{
			var type = dataSelect[i];
			if (this.phoneCrm[type])
			{
				if (type == 'CONTACT_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_CONTACT'),
						sort: 200+i,
						eventName: "crm_contact_"+this.phoneCrm[type].ID
					};
				}
				else if (type == 'COMPANY_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_COMPANY'),
						sort: 200+i,
						eventName: "crm_company_"+this.phoneCrm[type].ID
					};
				}
				else if (type == 'LEAD_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_LEAD'),
						sort: 200+i,
						eventName: "crm_lead_"+this.phoneCrm[type].ID
					};
				}
				middleChange = true;
			}
		}
		if (middleChange)
		{
			BX.MobileCallUI.form.updateMiddle(middleLabels, middleButtons);
		}
	}
	else
	{
		this.phoneLog('CRM NOT FOUND');

		var headerLabels = {};
		if (this.phoneCallConfig.RECORDING == "Y")
		{
			headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_ON'), textColor: "#ecd748"};
		}
		else
		{
			headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
		}

		BX.MobileCallUI.form.updateHeader(headerLabels);

		var middleButtons = {
			button1: {
				text: BX.message('IM_CRM_BTN_NEW_LEAD'),
				sort:100,
				eventName: "button1"
			},
			button2: {
				text: BX.message('IM_CRM_BTN_NEW_CONTACT'),
				sort:1,
				eventName: "button2"
			}
		};
		//BX.MobileCallUI.form.updateMiddle({}, middleButtons);
	}
}

BX.ImWebRTCMobile.prototype.callOverlayClose = function()
{
	BX.MobileCallUI.numpad.close();
	BX.MobileCallUI.form.close();
}

BX.ImWebRTCMobile.prototype.phoneToggleAudio = function(status)
{
	if (!this.phoneCurrentCall)
		return false;

	if (status)
	{
		this.phoneCurrentCall.muteMicrophone()
	}
	else
	{
		this.phoneCurrentCall.unmuteMicrophone()
	}

	this.phoneMicMuted = status;
}

BX.ImWebRTCMobile.prototype.phoneToggleSpeaker = function(status)
{
	if (!this.phoneCurrentCall)
		return false;

	this.phoneCurrentCall.setUseLoudSpeaker(status)

	this.phoneSpeakerEnable = status;
}

BX.ImWebRTCMobile.prototype.phoneSupport = function()
{
	return this.phoneEnabled && app.enableInVersion(14) && typeof(BX.MobileVoximplant) != 'undefined';
}

BX.ImWebRTCMobile.prototype.callAbort = function(reason)
{
	this.callOverlayDeleteEvents();

	if (reason)
		this.callOverlayStatus(reason);
}

BX.ImWebRTCMobile.prototype.phoneCallFinish = function()
{
	BX.MessengerCommon.phoneCallFinish();

	this.callOverlayTimer('pause');

	this.initiator = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callInit = false;
	this.callInitUserId = 0;
	this.callActive = false;
	this.audioMuted = false;
}

BX.ImWebRTCMobile.prototype.callOverlayDeleteEvents = function()
{
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

	BX.onCustomEvent(window, 'onImCallEnd', [{'CALL_ID': callId}]);

	this.callToMobile = false;
	this.callToPhone = false;

	this.phoneCallFinish();

	clearTimeout(this.callInviteTimeout);
}

BX.ImWebRTCMobile.prototype.phoneLog = function()
{
	console.log('Phone Log', JSON.stringify(arguments));
}

})();