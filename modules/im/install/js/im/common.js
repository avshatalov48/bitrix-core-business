/* eslint-disable */
;(function (window)
{
	if (window.BX.MessengerCommon) return;

	var BX = window.BX;

	var MessengerCommon = function ()
	{
		this.BXIM = {};
		this.sendBotCommand = false;
		this.sendBotCommandBlock = {};
		this.tryCheckConnect = {};
		this.externalLink = {};
	};

	/* Section: Context */
	MessengerCommon.prototype.setBxIm = function(dom)
	{
		this.BXIM = dom;
	}

	MessengerCommon.prototype.isIntranet = function()
	{
		return this.BXIM.bitrixIntranet;
	}

	MessengerCommon.prototype.isPage = function()
	{
		//return typeof(BX.MessengerWindow) != 'undefined' && !(this.BXIM.context == 'POPUP-FULLSCREEN'/* && BX.browser.IsMobile()*/); // TODO
		return typeof(BX.MessengerWindow) != 'undefined';
	}

	MessengerCommon.prototype.isPopupPage = function()
	{
		return (
			typeof(BX.MessengerWindow) != 'undefined'
			&& this.BXIM.bitrixIntranet
			&& this.BXIM.context == 'POPUP-FULLSCREEN'
		);
	}

	MessengerCommon.prototype.isDesktop = function()
	{
		return typeof(BX.desktop) != 'undefined' && BX.desktop.apiReady;
	}

	MessengerCommon.prototype.getDefaultZIndex = function()
	{
		var zIndex = 1000;
		if (typeof BX.SidePanel !== 'undefined' && BX.SidePanel.Instance.isOpen())
		{
			var topSlider = BX.SidePanel.Instance.getTopSlider();
			if (topSlider)
			{
				zIndex = topSlider.getZindex() - BX.PopupWindow.getOption("popupZindex");
			}
		}

		return zIndex;
	}

	MessengerCommon.prototype.isSliderEnable = function()
	{
		return typeof BX.SidePanel !== 'undefined';
	}

	MessengerCommon.prototype.isSliderSupport = function()
	{
		return (
			this.isSliderEnable()
			&& (
				!this.isDesktop()
				|| this.isDesktop() && BX.desktop.enableInVersion(44)
			)
		);
	}

	MessengerCommon.prototype.isSliderBindingsEnable = function()
	{
		return (
			this.isSliderSupport()
			&& typeof BX.SidePanel.Instance.isAnchorBinding !== 'undefined'
		);
	}

	MessengerCommon.prototype.isMobile = function()
	{
		return this.BXIM.mobileVersion;
	}

	MessengerCommon.prototype.hideLinesKeyboard = function()
	{
		if (this.textPanelShowed)
		{
			this.textPanelShowed = false;
			BXMobileApp.UI.Page.TextPanel.hide();
		}
	}

	MessengerCommon.prototype.isSessionBlocked = function(chatId)
	{
		var session = BX.MessengerCommon.linesGetSession(this.BXIM.messenger.chat[chatId]);

		if (session && session.blockDate !== 0 && new Date(session.blockDate * 1000) < new Date())
		{
			return true;
		}
		return false;
	}

	MessengerCommon.prototype.isMobileNative = function()
	{
		return false;
	}

	MessengerCommon.prototype.isLinesOperator = function()
	{
		return this.BXIM.isLinesOperator;
	}

	MessengerCommon.prototype.isBot = function(botId)
	{
		return typeof(this.BXIM.messenger.bot[botId]) != 'undefined';
	}

	MessengerCommon.prototype.isChatId = function(dialogId)
	{
		return /^(chat|sg|crm)[0-9]{1,}/i.test(dialogId);
	}

	MessengerCommon.prototype.isDialogId = function(dialogId)
	{
		return /^([0-9]{1,}|(chat|sg|crm)[0-9]{1,})/i.test(dialogId);
	}

	MessengerCommon.prototype.applyViewCommonUsers = function(active)
	{
		if (typeof active === 'boolean')
		{
			this.BXIM.settings.viewCommonUsers = active;
		}

		if (!this.BXIM.init)
		{
			return true;
		}

		if (!this.BXIM.settings.viewCommonUsers)
		{
			this.BXIM.messenger.recent = this.BXIM.messenger.recent.filter(function(element) {
				return !(element.invited || element.options.default_user_record);
			});
			this.recentListBirthdayApply();
			this.recentListRedraw();

			return true;
		}

		this.BXIM.messenger.recentLoadMore = true;
		this.recentListRedraw();

		BX.rest.callBatch({
			recent: ['im.recent.list', {
				'SKIP_NOTIFICATION': 'Y',
				'SKIP_OPENLINES': (BX.MessengerCommon.isLinesOperator()? 'Y': 'N'),
			}],
			counters: ['im.counters.get', {'JSON': 'Y'}],
		}, function (result){
			if (result.counters.error())
			{
				BX.UI.Notification.Center.notify({
					content: BX.message('IM_CONNECT_ERROR'),
					autoHideDelay: 4000
				});
				return false;
			}

			this.recentListApply(result.recent.data(), result.counters.data());
			this.recentListRedraw();

			if (this.BXIM.messenger.checkRecentNeedLoad())
			{
				this.BXIM.messenger.recentListLoadMore();
			}
		}.bind(this));

		return true;
	}

	MessengerCommon.prototype.applyBirthdaySettings = function(active)
	{
		if (typeof active === 'boolean')
		{
			this.BXIM.settings.viewBirthday = active;
		}

		if (!this.BXIM.init)
		{
			return true;
		}

		this.recentListBirthdayApply();
		this.recentListRedraw();

		return true;
	}

	MessengerCommon.prototype.isBirthdayEnable = function()
	{
		if (this.BXIM.messenger.birthdayEnable === 'none')
		{
			return false;
		}

		if (!this.BXIM.settings.viewBirthday)
		{
			return false;
		}

		return true;
	}

	MessengerCommon.prototype.isBirthday = function(birthday, userId) // after change this code, sync with IM and MOBILE
	{
		if (!this.isBirthdayEnable())
		{
			return false;
		}

		if (
			this.BXIM.messenger.birthdayEnable === 'department'
			&& userId
			&& !this.BXIM.messenger.birthdayUsers[userId]
		)
		{
			return false;
		}

		var date = new Date();
		var currentDate = ("0" + date.getDate().toString()).substr(-2)+'-'+("0" + (date.getMonth() + 1).toString()).substr(-2);
		return birthday == currentDate;
	};

	MessengerCommon.prototype.checkInternetConnection = function (successCallback, failureCallback, tryCount, tryName)
	{
		if (typeof(successCallback) != 'function')
		{
			successCallback = function ()
			{
				if (typeof(BXIM) != 'undefined')
				{
					BXIM.messenger.connectionStatus('online', false);
				}
			};
		}

		if (typeof(failureCallback) != 'function')
			failureCallback = function() {};

		if (typeof(tryCount) != "number")
			tryCount = 1;

		if (!tryName && tryCount > 1)
			tryName = +new Date();

		if (typeof(BXIM) != 'undefined')
		{
			BXIM.messenger.connectionStatus('connecting');
		}

		BX.ajax({
			url: '//www.bitrixsoft.com/200.ok.'+(+new Date),
			method: 'GET',
			dataType: 'html',
			skipAuthCheck: true,
			skipBxHeader: true,
			timeout: 1,
			onsuccess: function(data){
				if (data == 'OK')
				{
					console.log('Checking internet connection... success!');
					delete BX.MessengerCommon.tryCheckConnect[tryName];
					successCallback();
				}
				else
				{
					if (typeof(BXIM) != 'undefined')
					{
						BXIM.messenger.connectionStatus('offline');
					}

					console.log('Checking internet connection... failure!');
					if (tryCount == 1)
					{
						delete BX.MessengerCommon.tryCheckConnect[tryName];
						failureCallback();
					}
					else
					{
						if (typeof(BXIM) != 'undefined')
						{
							BXIM.messenger.connectionStatus('connecting');
						}
						clearTimeout(BX.MessengerCommon.tryCheckConnect[tryName]);
						BX.MessengerCommon.tryCheckConnect[tryName] = setTimeout(function(){
							BX.MessengerCommon.checkInternetConnection(successCallback, failureCallback, tryCount-1, tryName)
						}, 5000);
					}
				}
			},
			onfailure: function(){
				console.log('Checking internet connection... failure!');
				if (tryCount == 1)
				{
					delete BX.MessengerCommon.tryCheckConnect[tryName];
					failureCallback();
				}
				else
				{
					clearTimeout(BX.MessengerCommon.tryCheckConnect[tryName]);
					BX.MessengerCommon.tryCheckConnect[tryName] = setTimeout(function(){
						BX.MessengerCommon.checkInternetConnection(successCallback, failureCallback, tryCount-1, tryName)
					}, 5000);
				}
			}
		});

		return true;
	}

	MessengerCommon.prototype.pinDialog = function(dialogId, active)
	{
		this.recentListElementPin(dialogId, active);
		BX.rest.callMethod('im.recent.pin', {'DIALOG_ID': dialogId, 'ACTION': active? 'Y': 'N'});
	};

	MessengerCommon.prototype.muteMessageChat = function(dialogId, mute, sendAjax)
	{
		var chatId = 0;
		if (dialogId.toString().substr(0,4) == 'chat')
		{
			chatId = dialogId.toString().substr(4);
			if (!this.BXIM.messenger.chat[chatId])
				return false;
		}
		else
		{
			chatId = this.BXIM.messenger.userChat[dialogId];
			if (!chatId)
				return false;
		}

		sendAjax = sendAjax != false;

		if (!this.BXIM.messenger.userChatBlockStatus[chatId])
			this.BXIM.messenger.userChatBlockStatus[chatId] = {}

		if (typeof mute == 'undefined')
		{
			if (typeof this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] == 'undefined')
			{
				mute = true
			}
			else
			{
				mute = !this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId];
			}
		}
		else
		{
			mute = Boolean(mute);
		}

		this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] = mute;
		if (
			this.BXIM.messenger.chat[chatId]
			&& this.BXIM.messenger.chat[chatId].mute_list
		)
		{
			this.BXIM.messenger.chat[chatId].mute_list[this.BXIM.userId] = mute;
		}

		this.userListRedraw();
		this.BXIM.messenger.dialogStatusRedraw();
		this.BXIM.messenger.updateMessageCount();

		var muteAction = this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]? 'Y':'N';

		if (sendAjax)
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?CHAT_MUTE&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
					name: 'im.chat.mute',
					dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
					data: {
						timMuteAction: muteAction
					}
				}),
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_CHAT_MUTE' : 'Y', 'CHAT_ID': chatId, 'MUTE': muteAction, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}
	};

	MessengerCommon.prototype.MobileActionEqual = function(action)
	{
		if (!this.isMobile())
			return true;

		for (var i = 0; i < arguments.length; i++)
		{
			if (arguments[i] == this.BXIM.mobileAction)
				return true;
		}

		return false;
	}

	MessengerCommon.prototype.MobileActionNotEqual = function(action)
	{
		if (!this.isMobile())
			return false;

		for (var i = 0; i < arguments.length; i++)
		{
			if (arguments[i] == this.BXIM.mobileAction)
				return false;
		}

		return true;
	}

	MessengerCommon.prototype.isScrollMax = function(element, infelicity)
	{
		if (!element) return true;
		infelicity = typeof(infelicity) == 'number'? infelicity: 0;

		if (this.isMobile())
		{
			var height = window.orientation == 0? screen.height-125: screen.width-113;
			return (document.body.scrollHeight - height - height/2 <= element.scrollTop);
		}
		else
		{
			return (element.scrollHeight - element.offsetHeight - infelicity <= element.scrollTop);
		}

	};

	MessengerCommon.prototype.isScrollMin = function(element)
	{
		if (!element) return false;
		return (0 == element.scrollTop);
	};

	MessengerCommon.prototype.enableScroll = function(element, max, scroll)
	{
		if (!element)
			return false;

		if (this.BXIM.messenger.isBodyScroll)
			return false;

		if (this.isMobile())
		{
			return true;
		}

		scroll = scroll !== false;
		max = 400;

		if (!(scroll && this.isScrollMax(element, max)))
		{
			return false;
		}

		var lastUnreadMessage = (
			this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab]
			&& this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab][0]
				? BX('im-message-'+this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab][0])
				: null
		)
		if (lastUnreadMessage)
		{
			var visibleNode = lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode.previousElementSibling? lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode.previousElementSibling: lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode;
			var scrollResult = this.isElementVisibleOnScreen(visibleNode, element, true);
			if (
				!scrollResult.top
				|| (scrollResult.coords.top > 0 && scrollResult.coords.top < 150)
				|| (scrollResult.coords.top < 0)
			)
			{
				return false;
			}
		}

		return true;
	};

	MessengerCommon.prototype.preventDefault = function(event)
	{
		event = event||window.event;

		if (event.stopPropagation)
			event.stopPropagation();
		else
			event.cancelBubble = true;

		if (typeof(BXIM) != 'undefined' && BXIM.messenger && BXIM.messenger.closeMenuPopup)
			BXIM.messenger.closeMenuPopup();

		if (typeof(BX) != 'undefined' && BX.calendar && BX.calendar.get().popup)
			BX.calendar.get().popup.close();
	};

	MessengerCommon.prototype.countObject = function(obj)
	{
		var result = 0;

		for (var i in obj)
		{
			if (obj.hasOwnProperty(i))
			{
				result++;
			}
		}

		return result;
	};

	/* Section: Element Coords */
	MessengerCommon.prototype.isElementCoordsBelow = function (element, domBox, offset, returnArray)
	{
		if (this.isMobile())
		{
			return true;
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		offset = offset? offset: 0;

		var coords = this.getElementCoords(element, domBox);
		coords.bottom = coords.top+element.offsetHeight;

		var topVisible = (coords.top >= offset);
		var bottomVisible = (coords.bottom > offset);

		if (returnArray)
		{
			return {'top': topVisible, 'bottom': bottomVisible, 'coords': coords};
		}
		else
		{
			return (topVisible || bottomVisible);
		}
	}

	MessengerCommon.prototype.isElementVisibleOnScreen = function (element, domBox, returnObject)
	{
		if (this.isMobile())
		{
			return BitrixMobile.Utils.isElementVisibleOnScreen(element);
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		var coords = this.getElementCoords(element, domBox);
		coords.bottom = coords.top+element.offsetHeight;

		var windowTop = domBox.scrollTop;
		var windowBottom = windowTop + domBox.clientHeight;

		var topVisible = (coords.top >= 0 && coords.top < windowBottom);
		var bottomVisible = (coords.bottom > 0 && coords.bottom < domBox.clientHeight);

		if (returnObject)
		{
			return {'result':  (topVisible || bottomVisible), 'top': topVisible, 'bottom': bottomVisible, 'coords': coords};
		}
		else
		{
			return (topVisible || bottomVisible);
		}
	}

	MessengerCommon.prototype.getElementCoords = function (element, domBox)
	{
		if (this.isMobile())
		{
			return BitrixMobile.Utils.getElementCoords(element);
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		var box = element.getBoundingClientRect();
		var inBox = domBox.getBoundingClientRect();

		return {
			originTop: box.top,
			originLeft: box.left,
			top: box.top - inBox.top,
			left: box.left - inBox.left
		};
	}



	/* Section: Date */
	MessengerCommon.prototype.getDateFormatType = function(type)
	{
		type = type? type.toString().toUpperCase(): 'DEFAULT';

		var format = [];
		if (type == 'MESSAGE_TITLE')
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(BX.message("IM_M_MESSAGE_TITLE_FORMAT_DATE"))]
			];
		}
		else if (type == 'MESSAGE')
		{
			format = [
				["", BX.message("IM_M_MESSAGE_FORMAT_TIME")]
			];
		}
		else if (type == 'RECENT_TITLE')
		{
			format = [
				["tommorow", "today"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(BX.message("IM_CL_RESENT_FORMAT_DATE"))]
			]
		}
		else if (type == 'RECENT_OL_TITLE')
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(BX.message("IM_CL_RESENT_FORMAT_DATE"))]
			]
		}
		else
		{
			format = [
				["tommorow", "tommorow, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["today", "today, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["yesterday", "yesterday, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["", BX.Main.Date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))]
			];
		}
		return format;
	}

	MessengerCommon.prototype.formatDate = function(date, format)
	{
		if (typeof(format) == 'undefined')
		{
			format = this.getDateFormatType('DEFAULT')
		}
		if (!BX.type.isDate(date))
		{
			if (typeof date == 'string')
			{
				date = new Date(date);
			}
			console.log(date, format);
			console.trace();
			return '';
		}
		return BX.Main.Date.format(format, Math.round(date.getTime()/1000)+parseInt(BX.message("SERVER_TZ_OFFSET"))+parseInt(BX.message("USER_TZ_OFFSET")), Math.round((new Date).getTime()/1000)+parseInt(BX.message("SERVER_TZ_OFFSET"))+parseInt(BX.message("USER_TZ_OFFSET")), true);
	};

	MessengerCommon.prototype.getNowDate = function(today)
	{
		var currentDate = new Date();
		if (today === true)
		{
			currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate(), 0, 0, 0);
		}

		return currentDate;
	};

	MessengerCommon.prototype.replaceDateText = function(messageId, messageText, messageParams)
	{
		if (
			!messageParams.DATE_TEXT
			|| !messageParams.DATE_TS
		)
		{
			return messageText;
		}

		var textReplacement = [];
		messageText = messageText.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/gi, function(whole)
		{
			var id = textReplacement.length;
			textReplacement.push(whole);
			return '####REPLACEMENT_TEXT_'+id+'####';
		});

		messageText = messageText.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/gi, function(whole)
		{
			var id = textReplacement.length;
			textReplacement.push(whole);
			return '####REPLACEMENT_TEXT_'+id+'####';
		});

		messageText = messageText.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/gi, function(whole)
		{
			var id = textReplacement.length;
			textReplacement.push(whole);
			return '####REPLACEMENT_TEXT_'+id+'####';
		});

		messageParams.DATE_TEXT.forEach(function(date, index)
		{
			if (!date)
			{
				return true;
			}
			var ts = messageParams.DATE_TS[index] || +new Date;

			messageText = messageText.split(date).join('<span class="bx-messenger-ajax bx-messenger-ajax-black" data-entity="date" data-messageId="'+messageId+'" data-ts="'+ts+'">'+date+'</span>');
		}.bind(this));

		if (textReplacement.length > 0)
		{
			do
			{
				for (var index = 0; index < textReplacement.length; index++)
				{
					messageText = messageText.replace('####REPLACEMENT_TEXT_'+index+'####', textReplacement[index]);
				}
			}
			while (messageText.indexOf('####REPLACEMENT_TEXT_') > -1);
		}

		return messageText;
	}

	MessengerCommon.prototype.toBXUrl = function(url)
	{
		const isMobileWebComponent = this.isMobile() && this.BXIM.webComponent;
		const isCurrentDomainUrl = (
			currentDomain
			&& typeof(url) === 'string'
			&& url !== ''
			&& url.startsWith(currentDomain)
		);

		if (isMobileWebComponent && isCurrentDomainUrl)
		{
			return `bx${url}`;
		}

		return url;
	};

	/* Section: Images */
	MessengerCommon.prototype.formatUrl = function(url)
	{
		if (this.isMobile() && this.BXIM.webComponent && currentDomain)
		{
			if (url && url.indexOf('/') === 0)
			{
				url = currentDomain + url;
			}
		}

		return encodeURI(url);
	};

	MessengerCommon.prototype.isBlankAvatar = function(url)
	{
		return !url || url.toString().indexOf(this.BXIM.pathToBlankImage) >= 0;
	};

	MessengerCommon.prototype.getDefaultAvatar = function(type)
	{
		return "/bitrix/js/im/images/default-avatar-"+type+".png";
	};

	MessengerCommon.prototype.getAvatarStyle = function(entity, onlyStyle)
	{
		onlyStyle = !!onlyStyle;

		if (BX.MessengerCommon.isBlankAvatar(entity.avatar))
		{
			avatarStyle = 'background-color: '+entity.color;
		}
		else
		{
			avatarStyle = 'background: url(\''+entity.avatar+'\'); background-size: cover;';
		}

		if (!onlyStyle)
		{
			avatarStyle = 'style="'+avatarStyle+'"';
		}

		return avatarStyle;
	};

	MessengerCommon.prototype.hideErrorImage = function(element, rich)
	{
		if (rich)
		{
			BX.remove(element.parentNode);
			return true;
		}

		var link = element.src;
		if (element.parentNode && element.parentNode.parentNode)
		{
			element.parentNode.parentNode.className = 'bx-messenger-message';
			element.parentNode.parentNode.innerHTML = BX.create('a', {attrs: {href: link, target: "_blank"}, text: decodeURI(link)}).outerHTML;

		}

		return true;
	};



	/* Section: Text */
	MessengerCommon.prototype.prepareText = function(text, prepare, quote, image, highlightText, objectReference)
	{
		if (!text)
		{
			return text;
		}

		var textElement = text;
		prepare = prepare == true;
		quote = quote == true;
		image = image == true;
		highlightText = false; // deprecated

		textElement = BX.util.trim(textElement);

		if (prepare)
		{
			textElement = BX.util.htmlspecialchars(textElement);
		}

		if (textElement.indexOf('/me') == 0)
		{
			textElement = textElement.substr(4);
			textElement = '<i>'+textElement+'</i>';
		}
		else if (textElement.indexOf('/loud') == 0)
		{
			textElement = textElement.substr(6);
			textElement = '<b>'+textElement+'</b>';
		}

		textElement = this.decodeBbCode(textElement);

		if (prepare)
		{
			textElement = textElement.replace(/\n/gi, '<br />');
		}

		if (quote)
		{
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\](?: #(?:(?:chat)?\d+|\d+:\d+)\/\d+)?<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, p4, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\"><div class=\"bx-messenger-content-quote-name\">"+p1+" <span class=\"bx-messenger-content-quote-time\">"+p2+"</span></div>"+p3+"</div></div><br />";
			});
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">"+p1+"</div></div><br />";
			});
		}

		var quoteSign = "&gt;&gt;";
		if(quote && textElement.indexOf(quoteSign) >= 0)
		{
			var textPrepareFlag = false;
			var textPrepare = textElement.split("<br />");
			for(var i = 0; i < textPrepare.length; i++)
			{
				if(textPrepare[i].substring(0,quoteSign.length) == quoteSign)
				{
					textPrepare[i] = textPrepare[i].replace(quoteSign, "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">");
					while(++i < textPrepare.length && textPrepare[i].substring(0,quoteSign.length) == quoteSign)
					{
						textPrepare[i] = textPrepare[i].replace(quoteSign, '');
					}
					textPrepare[i-1] += '</div></div>';
					textPrepareFlag = true;
				}
			}
			textElement = textPrepare.join("<br />");
		}

		textElement = textElement.replace(/( ){4}/gi, '\t');
		textElement = textElement.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		if (image)
		{
			var changed = false;
			textElement = textElement.replace(/>((https|http):\/\/(\S+)\.(jpg|jpeg|png|gif|webp)(\?\S+[^<])?)<\/a>/gi, function(whole, urlParsed)
			{
				const url = BX.Text.decode(urlParsed);

				if (
					!url.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i)
					|| url.toLowerCase().indexOf("/docs/pub/") > 0
					|| url.toLowerCase().indexOf("logout=yes") > 0
				)
				{
					return whole;
				}
				else if (BX.MessengerCommon.isMobile())
				{
					changed = true;
					return '><span class="bx-messenger-file-image"><span class="bx-messenger-file-image-src"><img src="'+url+'" class="bx-messenger-file-image-text" onclick="BXIM.messenger.openPhotoGallery(this.src);" onerror="BX.MessengerCommon.hideErrorImage(this)"></span></span></a>';
				}
				else
				{
					changed = true;

					var chatId = typeof(this.BXIM.messenger.getChatId) != 'undefined'? this.BXIM.messenger.getChatId(): this.BXIM.messenger.currentTab;
					return '><span class="bx-messenger-file-image"><span class="bx-messenger-file-image-src"><img src="'+url+'" data-viewer="null" data-viewer-group-by="'+chatId+'" data-title="'+BX.util.jsencode(url)+'" class="bx-messenger-file-image-text" onerror="BX.MessengerCommon.hideErrorImage(this)"></span></span></a>';
				}
			});
			if (changed)
			{
				textElement = textElement
					.replace(/<\/span>(\n?)<\/a>(\n?)<br(\s\/?)>/gi, '</span></a>')
					.replace(/<\/span>(\n?)(\n?)<br(\s\/?)>/gi, '</span>')
				;
			}
		}

		if (this.BXIM.settings.enableBigSmile)
		{
			var oneSmileInMessage = false;
			textElement = textElement.replace(
				/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/,
				function doubleSmileSize(match, start, width, middle, height, end) {
					oneSmileInMessage = true;
					return start + (parseInt(width, 10) * 1.6) + middle + (parseInt(height, 10) * 1.6) + end;
				}
			);
			if (objectReference && oneSmileInMessage)
			{
				objectReference.oneSmileInMessage = true;
			}
		}

		if (textElement.substr(-6) == '<br />')
		{
			textElement = textElement.substr(0, textElement.length-6);
		}
		textElement = textElement.replace(/<br><br \/>/gi, '<br />');
		textElement = textElement.replace(/<br \/><br>/gi, '<br />');

		return textElement;
	};

	MessengerCommon.prototype.trimText = function(text)
	{
		return BX.util.trim(text);
	};

	MessengerCommon.prototype.purifyText = function(text, params) // after change this code, sync with IM and MOBILE
	{
		text = text? text.toString(): '';

		if (text)
		{
			text = this.trimText(text);

			if (text.indexOf('/me') == 0)
			{
				text = text.substr(4);
			}
			else if (text.indexOf('/loud') == 0)
			{
				text = text.substr(6);
			}
			if (text.substr(-6) == '<br />')
			{
				text = text.substr(0, text.length-6);
			}

			text = text.replace(/<br><br \/>/gi, '<br />');
			text = text.replace(/<br \/><br>/gi, '<br />');

			text = text.replace(/\[CODE\]\n?([\0-\uFFFF]*?)\[\/CODE\]/gis, function(whole,text) {
				return '['+BX.message('IM_M_CODE_BLOCK')+'] ';
			});

			text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, function(match)
			{
				return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, function(match)
			{
				return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = this.recursiveReplace(text, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => text);
			text = this.recursiveReplace(text, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => text);
			text = this.recursiveReplace(text, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => text);
			text = this.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => text);

			text = text.replace(/\[url(?:=([^\[\]]+))?](.*?)\[\/url]/gis, (whole, link, text) => {return text? text: link;});
			text = text.replace(/\[url(?:=(.+))?](.*?)\[\/url]/gis, (whole, link, text) => {return text? text: link;});
			text = text.replace(/\[RATING=([1-5]{1})\]/gi, function(whole, rating) {return '['+BX.message('IM_F_RATING')+'] ';});
			text = text.replace(/\[ATTACH=([0-9]{1,})\]/gi, function(whole, rating) {return '['+BX.message('IM_F_ATTACH')+'] ';});
			text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER\]/gi, '$3');
			text = text.replace(/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/gi, '$2');
			text = text.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, message) => message);
			text = text.replace(/\[CALL=(.*?)](.*?)\[\/CALL\]/gi, '$2');
			text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, '$2');
			text = text.replace(/\[size=(\d+)](.*?)\[\/size]/gis, '$2');
			text = text.replace(/\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, '$2');
			text = text.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
			text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/gi, '($1)');
			text = text.replace(/<img.*?title="([^"]*)".*?>/gi, '($1)');
			text = text.replace(/\[ATTACH=([0-9]{1,})\]/gi, function(whole, command, text) {return command == 10000? '': '['+BX.message('IM_F_ATTACH')+'] ';});
			text = text.replace(/<s>([^"]*)<\/s>/gi, ' ');
			text = text.replace(/\[s\]([^"]*)\[\/s\]/gi, ' ');
			text = text.replace(/\[icon\=([^\]]*)\]/gi, function(whole)
			{
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
						title = '('+this.trimText(title)+')';
					}
				}
				else
				{
					title = '('+BX.message('IM_M_ICON')+')';
				}
				return title;
			}.bind(this));

			text = text.split('<br />')
				.map(function(element) { return element.replace(/(>>).+/gi, " ["+BX.message("IM_M_QUOTE_BLOCK")+"] ") })
				.join(' ')
				.replace(/<\/?[^>]+>/gi, '')
				.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, " ["+BX.message("IM_M_QUOTE_BLOCK")+"] ")
				.replace(/-{54}(.*?)-{54}/gs, "["+BX.message("IM_M_QUOTE_BLOCK")+"]")
			;

			text = this.trimText(text);
		}

		if (params && params.ATTACH && params.ATTACH.length > 0)
		{
			const attachText = [];

			let skipAttachBlock = false;
			params.ATTACH.forEach(element => {
				if (element.DESCRIPTION === 'SKIP_MESSAGE')
				{
					skipAttachBlock = true;
				}
				else if (element.DESCRIPTION)
				{
					attachText.push(this.purifyText(element.DESCRIPTION));
				}
			});

			if (!skipAttachBlock)
			{
				text = text
					+ (text? ' ': '')
					+ (attachText.length > 0? attachText.join(' '): '['+BX.message('IM_F_ATTACH')+']')
				;
			}
		}

		if (text.length <= 0)
		{
			if (params && (params.WITH_FILE || params.FILE_ID && params.FILE_ID.length > 0))
			{
				text = '['+BX.message('IM_F_FILE')+']';
			}
			else if (params && params.WITH_ATTACH)
			{
				text = '['+BX.message('IM_F_ATTACH')+']';
			}
			else
			{
				text = BX.message('IM_M_DELETED');
			}
		}

		return text;
	};

	MessengerCommon.prototype.decodeBbCode = function(textElement, textOnly, specialchars)
	{
		if (!textElement)
		{
			return textElement;
		}

		textElement = textElement.toString();

		textOnly = typeof(textOnly) === 'undefined'? false: textOnly;
		specialchars = typeof(specialchars) === 'undefined'? false: specialchars === true;

		if (specialchars)
		{
			textElement = BX.util.htmlspecialchars(textElement);
		}

		var putReplacement = [];
		textElement = textElement.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/gi, function(whole)
		{
			var id = putReplacement.length;
			putReplacement.push(whole);
			return '####REPLACEMENT_PUT_'+id+'####';
		});

		var sendReplacement = [];
		textElement = textElement.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/gi, function(whole)
		{
			var id = sendReplacement.length;
			sendReplacement.push(whole);
			return '####REPLACEMENT_SEND_'+id+'####';
		});

		var codeReplacement = [];
		textElement = textElement.replace(/\[CODE\]\n?([\0-\uFFFF]*?)\[\/CODE\]/gis, function(whole,text)
		{
			var id = codeReplacement.length;
			codeReplacement.push(text);
			return '####REPLACEMENT_CODE_'+id+'####';
		});

		// base pattern for urls
		textElement = textElement.replace(/\[url(?:=([^\[\]]+))?](.*?)\[\/url]/gis, function(whole, link, text)
		{
			link = BX.util.htmlspecialcharsback(link? link: text);

			try
			{
				var url = new URL(link, location.origin+location.pathname);
			}
			catch(e)
			{
				return whole;
			}

			var allowList = [
				"http:",
				"https:",
				"ftp:",
				"file:",
				"tel:",
				"callto:",
				"mailto:",
				"skype:",
				"viber:",
			];
			if (allowList.indexOf(url.protocol) <= -1)
			{
				return whole;
			}

			var tag = document.createElement('a');
			tag.href = url.href;
			tag.target = '_blank';
			tag.text = BX.util.htmlspecialcharsback(text);

			return tag.outerHTML;
		});

		// url like https://bitrix24.com/?params[1]="test"
		textElement = textElement.replace(/\[url(?:=(.+?[^[\]]))?](.*?)\[\/url]/gis, (whole, link, text) =>
		{
			link = BX.util.htmlspecialcharsback(link? link: text);

			try
			{
				var url = new URL(link, location.origin+location.pathname);
			}
			catch(e)
			{
				return whole;
			}

			var allowList = [
				"http:",
				"https:",
				"ftp:",
				"file:",
				"tel:",
				"callto:",
				"mailto:",
				"skype:",
				"viber:",
			];
			if (allowList.indexOf(url.protocol) <= -1)
			{
				return whole;
			}

			url = url.href;

			if (!url.slice(url.lastIndexOf('[')).includes(']'))
			{
				if (text.startsWith(']'))
				{
					url = `${url}]`;
					text = text.slice(1);
				}
				else if (text.startsWith('='))
				{
					const urlPart = BX.Text.decode(text.slice(1, text.lastIndexOf(']')));
					url = `${url}]=${urlPart}`;
					text = text.slice(text.lastIndexOf(']')+1);
				}
			}

			return BX.Dom.create({
				tag: 'a',
				attrs: {
					href: url,
					target: '_blank'
				},
				html: text
			}).outerHTML;
		});

		textElement = textElement.replace(/\[BR\]/gi, '<br/>');

		textElement = this.recursiveReplace(textElement, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => '<b>'+text+'</b>');
		textElement = this.recursiveReplace(textElement, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => '<u>'+text+'</u>');
		textElement = this.recursiveReplace(textElement, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => '<i>'+text+'</i>');
		textElement = this.recursiveReplace(textElement, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => '<s>'+text+'</s>');

		textElement = textElement.replace(/\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => {
			number = Number.parseInt(number, 10);
			if (number <= 8)
			{
				number = 8;
			}
			else if (number >= 30)
			{
				number = 30;
			}

			return BX.Dom.create({
				tag: 'span',
				style: { fontSize: number + 'px' },
				html: text
			}).outerHTML;
		});

		textElement = textElement.replace(/\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => {
			return BX.Dom.create({
				tag: 'span',
				style: { color: '#'+ hex },
				html: text
			}).outerHTML;
		});

		textElement = textElement.replace(/\[LIKE\]/gi, '<span class="bx-smile bx-im-smile-like" title="'+BX.message('IM_MESSAGE_LIKE')+'"></span>');
		textElement = textElement.replace(/\[DISLIKE\]/gi, '<span class="bx-smile bx-im-smile-dislike" title="'+BX.message('IM_MESSAGE_DISLIKE')+'"></span>');

		textElement = textElement.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER\]/gi, BX.delegate(function(whole, userId, replace, text)
		{
			var html = '';

			if (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "livechat")
				return text;

			userId = parseInt(userId);
			if (!textOnly && text && userId > 0)
				html = '<span class="bx-messenger-ajax '+(userId == this.BXIM.userId? 'bx-messenger-ajax-self': '')+'" data-entity="user" data-userId="'+userId+'">'+text+'</span>';
			else
				html = text;

			return html;
		}, this));

		textElement = textElement.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/gi, function(whole, openlines, chatId, text)
		{
			var html = '';

			chatId = parseInt(chatId);

			if (!textOnly && text && chatId > 0 && typeof(BXIM) != 'undefined')
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

		textElement = textElement.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, text) => {
			return text;
		});

		textElement = textElement.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, function(whole, historyId, text)
		{
			var html = '';

			historyId = parseInt(historyId);
			if (!textOnly && text && historyId > 0)
				html = '<span class="bx-messenger-ajax" data-entity="phoneCallHistory" data-historyId="'+historyId+'">'+text+'</span>';
			else
				html = text;

			return html;
		});


		textElement = textElement.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, function(whole, command, text)
		{
			var html = '';

			text = text? text: command;
			command = command? command: text;
			if (!textOnly && text)
				html = '<span class="bx-messenger-command" data-entity="call" data-command="'+BX.util.htmlspecialchars(command)+'">'+text+'</span>';
			else
				html = text;

			return html;
		});

		var textElementSize = 0;
		if (this.BXIM.settings.enableBigSmile)
		{
			textElementSize = BX.util.trim(textElement.replace(/\[icon\=([^\]]*)\]/gi, '')).length;
		}

		textElement = textElement.replace(/\[icon\=([^\]]*)\]/gi, BX.delegate(function(whole)
		{
			var url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);
			if (url && url[1])
			{
				url = url[1];
			}
			else
			{
				return '';
			}
			var attrs = {'src': url, 'border': 0};

			var size = whole.match(/size\=(\d+)/i);
			if (size && size[1])
			{
				attrs['width'] = size[1];
				attrs['height'] = size[1];
			}
			else
			{
				var width = whole.match(/width\=(\d+)/i);
				if (width && width[1])
				{
					attrs['width'] = width[1];
				}

				var height = whole.match(/height\=(\d+)/i);
				if (height && height[1])
				{
					attrs['height'] = height[1];
				}

				if (attrs['width'] && !attrs['height'])
				{
					attrs['height'] = attrs['width'];
				}
				else if (attrs['height'] && !attrs['width'])
				{
					attrs['width'] = attrs['height'];
				}
				else if (attrs['height'] && attrs['width'])
				{}
				else
				{
					attrs['width'] = 20;
					attrs['height'] = 20;
				}
			}

			attrs['width'] = attrs['width']>100? 100: attrs['width'];
			attrs['height'] = attrs['height']>100? 100: attrs['height'];

			if (this.BXIM.settings.enableBigSmile && textElementSize == 0 && attrs['width'] == attrs['height'] && attrs['width'] == 20)
			{
				attrs['width'] = 40;
				attrs['height'] = 40;
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
					attrs['title'] = title;
					attrs['alt'] = title;
				}
			}
			else
			{
				attrs['title'] = BX.message('IM_M_ICON');
				attrs['alt'] = attrs['title'];
			}

			return BX.create("img", {
				attrs: attrs,
				props : { className: "bx-smile bx-icon"}
			}).outerHTML;
		}, this));



		textElement = textElement.replace(/\[RATING\=([1-5]{1})\]/gi, BX.delegate(function(whole, rating)
		{
			return this.linesVoteHeadNodes(0, rating, false).outerHTML;
		}, this));

		//textElement = textElement.replace(/\*(.*?)\*/m, function(whole, text)
		//{
		//	return "<b>"+text+"</b>";
		//});
		//textElement = textElement.replace(/\_(.*?)\_/m, function(whole, text)
		//{
		//	return "<i>"+text+"</i>";
		//});
		//textElement = textElement.replace(/\~(.*?)\~/m, function(whole, text)
		//{
		//	return "<strike>"+text+"</strike>";
		//});

		const Parser = BX.Reflection.getClass('BX.Messenger.v2.Lib.Parser');
		if (Parser)
		{
			textElement = Parser.decodeSmileForLegacyCore(textElement, {enableBigSmile: this.BXIM.settings.enableBigSmile})
		}

		if (sendReplacement.length > 0)
		{
			for (var index = 0; index < sendReplacement.length; index++)
			{
				textElement = textElement.replace('####REPLACEMENT_SEND_'+index+'####', sendReplacement[index]);
			}
		}

		textElement = textElement.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, function(match)
		{
			return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, function(whole, command, text)
			{
				var html = '';

				text = text? text: command;
				command = (command? command: text).replace('<br />', '\n');

				if (!textOnly && text)
				{
					text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
					text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

					command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');

					html = '<span class="bx-messenger-command" data-entity="send" title="'+BX.message('IM_BB_SEND')+'">'+text+'</span>';
					html += '<span class="bx-messenger-command-data">'+command+'</span>';
				}
				else
				{
					html = text;
				}
				return html;
			});
		});

		if (putReplacement.length > 0)
		{
			for (var index = 0; index < putReplacement.length; index++)
			{
				textElement = textElement.replace('####REPLACEMENT_PUT_'+index+'####', putReplacement[index]);
			}
		}

		textElement = textElement.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, function(match)
		{
			return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, function(whole, command, text)
			{
				var html = '';

				text = text? text: command;
				command = (command? command: text).replace('<br />', '\n');

				if (!textOnly && text)
				{
					text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
					text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

					html = '<span class="bx-messenger-command" data-entity="put" title="'+BX.message('IM_BB_PUT')+'">'+text+'</span>';
					html += '<span class="bx-messenger-command-data">'+command+'</span>';
				}
				else
				{
					html = text;
				}

				return html;
			});
		});

		if (codeReplacement.length > 0)
		{
			for (var index = 0; index < codeReplacement.length; index++)
			{
				textElement = textElement.replace('####REPLACEMENT_CODE_'+index+'####',
					!textOnly? '<div class="bx-messenger-code">'+codeReplacement[index]+'</div>': codeReplacement[index]
				)
			}
		}

		if (sendReplacement.length > 0)
		{
			do
			{
				for (var index = 0; index < sendReplacement.length; index++)
				{
					textElement = textElement.replace('####REPLACEMENT_SEND_'+index+'####', sendReplacement[index]);
				}
			}
			while (textElement.indexOf('####REPLACEMENT_SEND_') > -1);
		}

		textElement = textElement.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

		if (putReplacement.length > 0)
		{
			do
			{
				for (var index = 0; index < putReplacement.length; index++)
				{
					textElement = textElement.replace('####REPLACEMENT_PUT_'+index+'####', putReplacement[index]);
				}
			}
			while (textElement.indexOf('####REPLACEMENT_PUT_') > -1);
		}

		return textElement;
	}

	MessengerCommon.prototype.recursiveReplace = function(text, pattern, replacement)
	{
		if (!BX.Type.isStringFilled(text))
		{
			return text;
		}

		let count = 0;
		let deep = true;
		do
		{
			deep = false;
			count++;
			text = text.replace(pattern, (...params) => {
				deep = true;
				return replacement(...params);
			});
		}
		while (deep && count <= 10);

		return text;
	}

	MessengerCommon.prototype.openLink = function(link, target)
	{
		target = target || '_blank';

		window.open(link, target, '', true);

		return true;

		// var dom = BX.create('a', {attrs: {href: link, style: 'display:none', target: target}});
		// document.body.appendChild(dom);
		// dom.click();
		// document.body.removeChild(dom);
		return true;
	}

	MessengerCommon.prototype.openNewTab = function(path)
	{
		const preparedPath = BX.Dom.create({ tag: 'a', attrs: { href: path } }).href;

		if (
			this.BXIM.desktop.enableInVersion(75)
			&& this.isDesktop()
			&& (
				location.href.includes('/desktop_app/')
				|| location.href.includes('&IM_TAB=Y')
			)
		)
		{
			BXDesktopSystem.CreateImTab(preparedPath + '&IM_TAB=Y');
		}
		else
		{
			this.openLink(preparedPath);
		}
	}

	MessengerCommon.prototype.clipboardCopy = function(callback, cut)
	{
		document.execCommand(cut == true? "cut": "copy");

		var clipboardTextArea = BX.create('textarea', { style : {'position': 'absolute', 'opacity': 0, 'top': -1000, 'left': -1000}});
		document.body.insertBefore(clipboardTextArea, document.body.firstChild);
		clipboardTextArea.focus();
		document.execCommand("paste");
		var text = clipboardTextArea.value;

		var textNew = null;
		if (typeof (callback) == 'function')
		{
			textNew = callback(clipboardTextArea.value);
		}
		else if (typeof (callback) != 'undefined')
		{
			textNew = callback.toString();
		}

		if (textNew)
		{
			text = clipboardTextArea.value = textNew;
			clipboardTextArea.selectionStart = 0;
			document.execCommand("copy");
		}

		BX.remove(clipboardTextArea);

		return text;
	}

	MessengerCommon.prototype.clipboardCut = function ()
	{
		return this.clipboardCopy(null, true);
	}

	MessengerCommon.prototype.prepareTextBack = function(text, trueQuote)
	{
		var textElement = text;

		trueQuote = trueQuote === true;

		textElement = BX.util.htmlspecialcharsback(textElement);
		textElement = textElement.replace(/<(\/*)([buis]+)>/gi, '[$1$2]');
		textElement = textElement.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
		textElement = textElement.replace(/<a.*?href="([^"]*)".*?>.*?<\/a>/gi, '$1');
		if (!trueQuote)
		{
			textElement = textElement.replace(/\[CODE\]\n?([\0-\uFFFF]*?)(<br\/?>)?\[\/CODE\]/gis, "["+BX.message("IM_M_CODE_BLOCK")+"]");
			textElement = textElement.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "["+BX.message("IM_M_QUOTE_BLOCK")+"]");
		}
		textElement = textElement.split('&nbsp;&nbsp;&nbsp;&nbsp;').join("\t");
		textElement = textElement.split('&nbsp;').join(" ");
		textElement = textElement.split('<br />').join("\n");//.replace(/<\/?[^>]+>/gi, '');

		return textElement;
	};

	MessengerCommon.prototype.addMentionList = function(tabId, dialogName, dialogId)
	{
		if (!tabId || !dialogName)
			return false;

		if (!this.BXIM.messenger.mentionList[tabId])
			this.BXIM.messenger.mentionList[tabId] = {};

		this.BXIM.messenger.mentionList[tabId][dialogName] = dialogId;
	}

	MessengerCommon.prototype.prepareMention = function(tabId, text)
	{
		if (!this.BXIM.messenger.mentionList[tabId])
			return text;

		for (var dialogName in this.BXIM.messenger.mentionList[tabId])
		{
			var dialogId = this.BXIM.messenger.mentionList[tabId][dialogName];
			if (!dialogId)
			{
				continue;
			}

			if (dialogId.toString().substr(0,4) == 'chat')
			{
				text = text.split(dialogName).join('[CHAT='+dialogId.toString().substr(4)+']'+dialogName+'[/CHAT]');
			}
			else
			{
				text = text.split(dialogName).join('[USER='+dialogId+']'+dialogName+'[/USER]');
			}
		}

		if (!BX.browser.IsIE11())
		{
			try
			{
				text = text.replace(RegExp('-{54}\n(.*?)\n-{54}', 'gs'), function(whole){
					return whole.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER\]/gi, '$3');
				});
			}
			catch(e) {}
		}

		this.clearMentionList(tabId);

		return text;
	}

	MessengerCommon.prototype.clearMentionList = function(tabId)
	{
		delete this.BXIM.messenger.mentionList[tabId];
	}



	/* Section: User state */
	MessengerCommon.prototype.getRecipientByChatId = function(chatId)
	{
		var recipientId = 0;
		if (this.BXIM.messenger.chat[chatId])
		{
			recipientId = 'chat'+chatId;
		}
		else
		{
			for (var userId in this.BXIM.messenger.userChat)
			{
				if (this.BXIM.messenger.userChat[userId] == chatId)
				{
					recipientId = userId;
					break;
				}
			}
		}
		return recipientId;
	}

	MessengerCommon.prototype.getUserIdByChatId = function(chatId)
	{
		var result = 0;
		for (var userId in this.BXIM.messenger.userChat)
		{
			if (this.BXIM.messenger.userChat[userId] == chatId)
			{
				result = userId;
				break;
			}
		}
		return result;
	}

	MessengerCommon.prototype.getUserParam = function(userId, reset)
	{
		userId = typeof(userId) == 'undefined'? this.BXIM.userId: userId;
		reset = typeof(reset) == 'boolean'? reset: false;

		if (
			userId && (
				userId.toString().substr(0, 4) == 'chat'
				|| userId.toString().substr(0, 2) == 'sg'
				|| userId.toString().substr(0, 3) == 'crm'
			)
		)
		{
			var chatId = userId.toString().substr(0, 4) == 'chat'? userId.toString().substr(4): userId;
			if (reset || !(this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].id))
			{
				this.BXIM.messenger.chat[chatId] = {'id': chatId, 'name': BX.message('IM_M_LOAD_USER'), 'owner': 0, work_position: '', 'avatar': this.BXIM.pathToBlankImage, 'type': 'chat', color: '#556574', mute_list: {}, 'fake': true, date_create: false};
				if (reset)
				{
					this.BXIM.messenger.chat[chatId].fake = false;
				}
			}
			return this.BXIM.messenger.chat[chatId];
		}
		else
		{
			if (reset || !(this.BXIM.messenger.users[userId] && this.BXIM.messenger.users[userId].id))
			{
				var profilePath = parseInt(userId)? this.BXIM.path.profileTemplate.replace('#user_id#', userId): '';
				this.BXIM.messenger.users[userId] = {'id': userId, 'avatar': this.BXIM.pathToBlankImage, 'name': BX.message('IM_M_LOAD_USER'), 'profile': profilePath, 'status': 'guest', work_position: '', 'extranet': false, 'network': false, color: '#556574', 'fake': true, last_activity_date: false, mobile_last_date: false, absent: false, idle: false};
				this.BXIM.messenger.hrphoto[userId] = '/bitrix/js/im/images/hidef-avatar-v3.png';
				if (reset)
				{
					this.BXIM.messenger.users[userId].fake = false;
				}
			}
			return this.BXIM.messenger.users[userId];
		}
	}

	MessengerCommon.prototype.userInChat = function(chatId, userId)
	{
		if (!BX.type.isArray(BXIM.messenger.userInChat[chatId]))
		{
			return false;
		}

		if (typeof(userId) == 'undefined')
		{
			userId = this.BXIM.userId;
		}
		else
		{
			userId = parseInt(userId);
		}

		var userFound = false;
		if (
			this.BXIM.messenger.userInChat[chatId].indexOf(userId.toString()) > -1
			|| this.BXIM.messenger.userInChat[chatId].indexOf(parseInt(userId)) > -1
		)
		{
			userFound = true;
		}

		return userFound;
	}

	MessengerCommon.prototype.onOnlineStatusCallback = function(userId, lastseen, now, utc, mode)
	{
		console.log('Run callback for', mode, userId, lastseen, now, utc);
	}

	MessengerCommon.prototype.getUserStatus = function(userData, onlyStatus) // after change this code, sync with IM and MOBILE
	{
		onlyStatus = onlyStatus !== false;

		var online = this.getOnlineData(userData);

		var status = 'offline';
		var statusText = '';
		var originStatus = '';
		var originStatusText = '';
		if (!userData)
		{
			status = 'guest';
			statusText = BX.message('IM_STATUS_GUEST');
		}
		else if (userData.network)
		{
			status = 'network';
			statusText = BX.message('IM_STATUS_NETWORK_MSGVER_1');

			if (userData.bot && this.BXIM.messenger.bot[userData.id] && this.BXIM.messenger.bot[userData.id].type == 'support24')
			{
				status = 'support24';
			}
		}
		else if (userData.bot)
		{
			status = 'bot';
			statusText = BX.message('IM_STATUS_BOT');
		}
		else if (userData.connector)
		{
			status = userData.status == 'offline'? 'lines': 'lines-online';
			statusText = BX.message('IM_CL_USER_LINES');
		}
		else if (userData.status == 'guest')
		{
			status = 'guest';
			statusText = BX.message('IM_STATUS_GUEST');
		}
		else if (this.getCurrentUser() == userData.id)
		{
			status = userData.status? userData.status.toString(): 'online';
			statusText = status? BX.message('IM_STATUS_'+status.toUpperCase()): '';
		}
		else if (!online.isOnline)
		{
			status = 'offline';
			statusText = BX.message('IM_STATUS_OFFLINE');
		}
		else if (this.getUserMobileStatus(userData))
		{
			status = 'mobile';
			statusText = BX.message('IM_STATUS_MOBILE');
		}
		else if (this.getUserIdleStatus(userData, online))
		{
			status = userData.status === 'break'? 'break-idle': 'idle';

			statusText = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userData));
		}
		else
		{
			status = userData.status? userData.status.toString(): 'offline';
			statusText = BX.message('IM_STATUS_'+status.toUpperCase());
		}

		if (userData && this.isBirthday(userData.birthday, userData.id) && (userData.status == 'online' || !online.isOnline))
		{
			originStatus = status;
			originStatusText = statusText;

			status = 'birthday';
			if (online.isOnline)
			{
				statusText = BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT');
			}
			else
			{
				statusText = BX.message('IM_STATUS_OFFLINE');
			}
		}
		else if (userData && userData.absent)
		{
			originStatus = status;
			originStatusText = statusText;

			status = 'vacation';
			if (online.isOnline)
			{
				statusText = BX.message('IM_STATUS_ONLINE');
			}
			else
			{
				statusText = BX.message('IM_STATUS_VACATION');
			}
		}

		return onlyStatus? status: {
			status: status,
			statusText: statusText,
			originStatus: originStatus? originStatus: status,
			originStatusText: originStatusText? originStatusText: statusText,
		};
	};

	MessengerCommon.prototype.getOnlineData = function(userData) // after change this code, sync with IM and MOBILE
	{
		var online = {};
		if (userData)
		{
			if (userData.id == this.getCurrentUser())
			{
				userData.last_activity_date = new Date();
				userData.mobile_last_date = false;
				userData.idle = false;
			}

			online = BX.user.getOnlineStatus(userData.last_activity_date);
		}

		return online;
	};

	MessengerCommon.prototype.getUserIdle = function(userData)
	{
		if (!userData)
		{
			return '';
		}

		var message = "";
		if (userData.idle)
		{
			var format = (new Date().getTime()-userData.idle.getTime())/1000 >= 3600? 'Hdiff': 'idiff';
			message = this.formatDate(userData.idle,[
			   ["s60", "sdiff"],
			   ["i60", "idiff"],
			   ["H24", "Hdiff"],
			   ["", "ddiff"]
			]);
		}

		return message;
	}

	MessengerCommon.prototype.getUserMobileStatus = function(userData) // after change this code, sync with IM and MOBILE
	{
		if (!userData)
			return false;

		return (
			userData.mobile_last_date
			&& new Date() - userData.mobile_last_date < parseInt(BX.message('LIMIT_ONLINE'))*1000
			&& userData.last_activity_date-userData.mobile_last_date < 300*1000
		);
	};

	MessengerCommon.prototype.getUserIdleStatus = function(userData, online) // after change this code, sync with IM and MOBILE
	{
		if (!userData)
			return '';

		online = online? online: BX.user.getOnlineStatus(userData.last_activity_date);

		return userData.idle && online.isOnline;
	};

	MessengerCommon.prototype.getUserPosition = function(userData, showLastActivityDate) // after change this code, sync with IM and MOBILE
	{
		showLastActivityDate = showLastActivityDate === true;

		if (!userData)
			return '';

		var position = '';
		if (showLastActivityDate && userData.last_activity_date && !(userData.bot || userData.network))
		{
			position = this.getUserLastDate(userData);
			if (position)
			{
				return position;
			}
		}

		if(userData.work_position)
		{
			position = userData.work_position;
		}
		else if (userData.extranet || userData.network)
		{
			position = BX.message('IM_CL_USER_EXTRANET');
		}
		else if (userData.bot)
		{
			position = BX.message('IM_CL_BOT');
		}
		else
		{
			position = this.isIntranet()? BX.message('IM_CL_USER'): BX.message('IM_CL_USER_B24');
		}

		return position;
	};

	MessengerCommon.prototype.getUserLastDate = function(userData)
	{
		if (!userData)
		{
			return '';
		}

		var text = '';
		var online = {};
		if (userData.bot || userData.network)
		{
			text = '';
		}
		else if (userData.absent && !this.getUserMobileStatus(userData))
		{
			online = this.getOnlineData(userData);
			text = BX.message('IM_STATUS_VACATION_TITLE').replace('#DATE#',
				BX.Main.Date.format(BX.Main.Date.convertBitrixFormat(BX.message("FORMAT_DATE")), userData.absent.getTime()/1000)
			);

			if (online.isOnline && userData.idle)
			{
				 text = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userData));
			}
			else if (online.isOnline && !online.lastSeenText)
			{
				text = BX.message('IM_STATUS_ONLINE')+'. '+text;
			}
			else if (online.lastSeenText)
			{
				text = BX.message('IM_LS_'+(userData.gender == 'F'? 'F': 'M')).replace('#POSITION#', text).replace('#LAST_SEEN#', online.lastSeenText);
			}
		}
		else if (userData.last_activity_date)
		{
			online = this.getOnlineData(userData);
			if (online.isOnline && userData.idle && !this.getUserMobileStatus(userData))
			{
				 text = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userData));
			}
			else if (online.isOnline && !online.lastSeenText)
			{
				if (this.isMobile() && this.getUserMobileStatus(userData))
				{
					text = BX.message('IM_STATUS_MOBILE');
				}
				else
				{
					text = BX.message('IM_STATUS_ONLINE');
				}
			}
			else if (online.lastSeenText)
			{
				text = BX.message('IM_LS_SHORT_'+(userData.gender == 'F'? 'F': 'M')).replace('#LAST_SEEN#', online.lastSeenText);
			}
		}

		return text;
	};

	MessengerCommon.prototype.isIntranet = function() {return this.BXIM.bitrixIntranet;};

	MessengerCommon.prototype.getCurrentUser = function() {return this.BXIM.userId;};

	MessengerCommon.prototype.getDialogId = function()
	{
		if (this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat')
		{
			return this.BXIM.messenger.currentTab;
		}

		return parseInt(this.BXIM.messenger.currentTab);
	};

	MessengerCommon.prototype.getLogTrackingParams = function(params)
	{
		if (typeof params !== 'object' || !params)
		{
			params = {};
		}

		var name = params.name || 'tracking';
		var data = params.data || [];
		var dialog = params.dialog || null;
		var message = params.message || null;
		var files = params.files || null;

		var result = [];

		name = encodeURIComponent(name);

		if (
			data
			&& !BX.type.isArray(data)
			&& typeof data === 'object'
		)
		{
			var dataArray = [];
			for (var id in data)
			{
				if (data.hasOwnProperty(id))
				{
					dataArray.push(encodeURIComponent(id)+"="+encodeURIComponent(data[id]));
				}
			}
			data = dataArray;
		}
		else if (!BX.type.isArray(data))
		{
			data = [];
		}

		if (dialog)
		{
			result.push('timType='+dialog.type);

			if (dialog.type === 'lines')
			{
				result.push('timLinesType='+dialog.entityId.split('|')[0]);
			}
		}

		if (files)
		{
			var type = 'file';
			if (BX.type.isArray(files) && files[0])
			{
				type = files[0].type;
			}
			else
			{
				type = files.type;
			}
			result.push('timMessageType='+type);
		}
		else if (message)
		{
			result.push('timMessageType=text');
		}

		if (navigator.userAgent && navigator.userAgent.toLowerCase().indexOf('bitrixmobile') > -1)
		{
			result.push('timDevice=bitrixMobile');
		}
		else if (navigator.userAgent && navigator.userAgent.toLowerCase().indexOf('bitrixdesktop') > -1)
		{
			result.push('timDevice=bitrixDesktop');
		}
		else if (
			navigator.userAgent.toLowerCase().indexOf('iphone') > -1
			|| navigator.userAgent.toLowerCase().indexOf('ipad') > -1
			|| navigator.userAgent.toLowerCase().indexOf('android') > -1
		)
		{
			result.push('timDevice=mobile');
		}
		else
		{
			result.push('timDevice=web');
		}

		return name + (data.length? '&'+data.join('&'): '') + (result.length? '&'+result.join('&'): '');
	}

	MessengerCommon.prototype.getDialogDataForTracking = function(dialogId)
	{
		var result = {type: 'private', entityId: '', entityTypeId: ''};

		if (dialogId.toString().indexOf('chat') === 0)
		{
			result.type = 'chat';

			var chatId = dialogId.toString().substr(4);
			if (this.BXIM.messenger.chat[chatId])
			{
				result.type = this.BXIM.messenger.chat[chatId].type;
				result.entityTypeId = this.BXIM.messenger.chat[chatId].entity_type_id;
				result.entityId = this.BXIM.messenger.chat[chatId].entity_id;
			}
		}

		return result;
	};

	MessengerCommon.prototype.getChatUsers = function()
	{
		if (this.BXIM.messenger.currentTab.toString().substr(0, 4) != 'chat')
		{
			return [].push(parseInt(this.BXIM.messenger.currentTab));
		}

		var chatId = this.BXIM.messenger.currentTab.toString().substr(4);
		var result = [];
		if (this.BXIM.messenger.userInChat[chatId])
		{
			result = this.BXIM.messenger.userInChat[chatId].map(function(item) {
				return parseInt(item);
			});
		}

		return result;
	};

	MessengerCommon.prototype.setColor = function(color, chatId)
	{
		if (!this.BXIM.init && this.isDesktop())
		{
			BX.desktop.onCustomEvent("bxSaveColor", [{color: color, chatId: chatId}]);
			return false;
		}

		if (typeof(color) != "string")
		{
			return false;
		}
		else
		{
			color = color.toUpperCase();
		}
		if (typeof(chatId) != 'undefined')
		{
			if (typeof(this.BXIM.messenger.chat[chatId]) == 'undefined')
			{
				return false;
			}
		}
		else
		{
			chatId = 0;
			if (this.BXIM.userColor == color)
			{
				return false;
			}
		}

		BX.ajax({
			url: this.BXIM.pathToAjax+'?SET_COLOR&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_SET_COLOR' : 'Y', 'COLOR' : color, 'CHAT_ID': chatId, 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				if (data.ERROR == "")
				{
					if (parseInt(data.CHAT_ID) == 0)
					{
						this.BXIM.userColor = data.COLOR;
						if (this.isPage())
						{
							setTimeout(function(){
								BX.MessengerWindow.setUserInfo(BX.MessengerCommon.getUserParam());
							}, 500);
						}
					}
				}
			}, this)
		});
	};

	MessengerCommon.prototype.checkRestriction = function(chatId, action)
	{
		if (!this.BXIM.messenger.chat[chatId])
			return null;

		if (!this.BXIM.messenger.chat[chatId].entity_type)
			return false;

		var entityType = this.BXIM.messenger.chat[chatId].entity_type;

		if (typeof(this.BXIM.messenger.userChatOptions[entityType]) == 'undefined' || typeof(this.BXIM.messenger.userChatOptions[entityType][action]) == 'undefined')
			return false;

		if (!this.BXIM.messenger.userChatOptions[entityType][action])
			return true;

		return false;
	}

	MessengerCommon.prototype.getEntityTypePath = function(chatId)
	{
		if (!this.BXIM.messenger.chat[chatId])
			return null;

		if (!this.BXIM.messenger.chat[chatId].entity_type)
			return null;

		var entityType = this.BXIM.messenger.chat[chatId].entity_type;

		if (entityType == 'CRM' && this.BXIM.bitrixCrm)
		{
			var entityParams = this.BXIM.messenger.chat[chatId].entity_id.toString().split('|');
			if (!this.BXIM.path.crm[entityParams[0]])
			{
				return null;
			}
			return {'PATH': this.BXIM.path.crm[entityParams[0]].replace('#ID#', entityParams[1]), 'TITLE': BX.message('IM_M_OL_GOTO_CRM')};
		}
		else
		{
			if (typeof(this.BXIM.messenger.userChatOptions[entityType]) == 'undefined')
				return null;

			if (!this.BXIM.messenger.userChatOptions[entityType]['PATH'])
				return null;

			return {'PATH': this.BXIM.messenger.userChatOptions[entityType]['PATH'].replace('#ID#', this.BXIM.messenger.chat[chatId].entity_id), 'TITLE': this.BXIM.messenger.userChatOptions[entityType]['PATH_TITLE']};
		}
	}

	MessengerCommon.prototype.renameChat = function(chatId, title)
	{
		chatId = parseInt(chatId);
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online' || !title || chatId <= 0)
			return false;

		title = BX.util.trim(title);
		if (title.length <= 0 || this.BXIM.messenger.chat[chatId].name == BX.util.htmlspecialchars(title))
			return false;

		var previousName = this.BXIM.messenger.chat[chatId].name;
		this.BXIM.messenger.chat[chatId].name = BX.util.htmlspecialchars(title);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_RENAME&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_CHAT_RENAME' : 'Y', 'CHAT_ID' : chatId, 'CHAT_TITLE': title, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				if (data.ERROR)
				{
					if (this.BXIM.messenger.popupMessengerPanelChatTitle)
					{
						this.BXIM.messenger.popupMessengerPanelChatTitle.innerHTML = previousName;
					}
					this.BXIM.messenger.chat[chatId].name = previousName;
				}
			}, this)
		});

		return true;
	};



	/* Section: CL & RL */
	MessengerCommon.prototype.userListRedraw = function(params)
	{
		if (this.isMobile())
		{
			if (!this.MobileActionEqual('RECENT'))
			{
				return false;
			}
		}

		if (
			this.BXIM.messenger.recentList
			&& this.BXIM.messenger.contactListSearchText != null
			&& this.BXIM.messenger.contactListSearchText.length == 0
		)
		{
			this.recentListRedraw(params);

			if (this.BXIM.messenger.checkRecentNeedLoad && this.BXIM.messenger.checkRecentNeedLoad())
			{
				this.BXIM.messenger.recentListLoadMore();
			}
		}
		else if (this.BXIM.messenger.chatList)
		{
			this.chatListRedraw(params);
		}
		else
		{
			this.contactListRedraw(params);
		}
	};



	/* Section: Concact List */
	MessengerCommon.prototype.contactListRedraw = function(params)
	{
		if (this.BXIM.messenger.popupMessenger == null)
			return false;

		params = params || {};

		if (!this.isMobile())
		{
			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.contactList = true;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.linesList = false;

			if (this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
			{
				this.BXIM.messenger.popupPopupMenu.close();
			}
		}

		if (this.BXIM.messenger.contactListSearchText.length > 0)
		{
			if (BX.MessengerProxy)
			{
				BX.MessengerProxy.sendOpenSearchEvent(this.BXIM.messenger.contactListSearchText);
			}
		}
		else
		{
			if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
				clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}

		// params.SEND = params.SEND == true;
		// if (!this.isMobile() && params.SEND)
		// {
		// 	BX.localStorage.set('mrd', {viewGroup: this.BXIM.settings.viewGroup, viewOffline: this.BXIM.settings.viewOffline}, 5);
		// }
	};

	MessengerCommon.prototype.contactListPrepareSearch = function(name, bind, search, params)
	{
		if (!bind)
			return false;

		if (
			this.BXIM.messenger.openLinesFlag &&
			(
				name == 'popupChatDialogContactListElements' && this.BXIM.messenger.popupChatDialogDestType == "CHAT_EXTEND" ||
				name == 'popupTransferDialogContactListElements'
			)
		)
		{
			params.viewOffline = true;
			params.viewOnlyIntranet = true;
			params.viewChat = false;
			params.viewOfflineWithPhones = false;
		}

		var searchParams = {
			'listName': name,
			'groupOpen': true,
			'viewSelf': name == 'contactList',
			'viewOffline': true,
			'viewGroup': true,
			'viewChat': true,
			'viewBot': true,
			'viewTransferViQueue': false,
			'viewTransferOlQueue': false,
			'viewOpenChat': true,
			'viewOfflineWithPhones': false,
			'showUserLastActivityDate': undefined,
			'extra': false,
			'searchText': search,
			'callback': {
				'empty': function(){}
			}
		};
		if (params != false)
		{
			for (var i in params)
			{
				if (i == 'timeout' || i == 'params')
					continue;

				searchParams[i] = params[i];
			}
		}

		var timeout = params.timeout? params.timeout: 0;

		if (timeout > 0)
		{
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout[name]);
			this.BXIM.messenger.redrawContactListTimeout[name] = setTimeout(BX.delegate(function(){
				bind.innerHTML = '';
				bind.appendChild(this.contactListPrepare(searchParams));
				if (this.isMobile())
				{
					BitrixMobile.LazyLoad.showImages();
				}
			}, this), timeout);
		}
		else
		{
			bind.innerHTML = '';
			bind.appendChild(this.contactListPrepare(searchParams));
			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}
	}

	MessengerCommon.prototype.contactListPrepare = function(params)
	{
		params = typeof(params) == 'object'? params: {};
		return this.chatListPrepare(params);
	};

	MessengerCommon.prototype.contactListClickItem = function(e)
	{
		if (
			this.BXIM.options.v2layout
			&& (
				e.metaKey || e.ctrlKey
			)
		)
		{
			const dialogId = BX.proxy_context.getAttribute('data-userId');
			this.openNewTab('/online/?IM_LINES=' + dialogId);

			return BX.PreventDefault(e);
		}

		this.BXIM.messenger.closeMenuPopup();
		var itemId = BX.proxy_context.getAttribute('data-userId');
		if (itemId.toString().substr(0,9) == 'structure')
		{
			var structureId = itemId.toString().substr(9);
			var structureName = this.BXIM.messenger.groups[structureId].name.split(' / ')[0];

			this.BXIM.messenger.popupContactListSearchInput.value = structureName;
			this.BXIM.messenger.contactListSearchText = itemId;

			this.contactListPrepareSearch('contactList', this.BXIM.messenger.popupContactListElementsWrap, this.BXIM.messenger.contactListSearchText, {})
			return BX.PreventDefault(e);
		}

		if (this.BXIM.messenger.contactList)
		{
			BX.MessengerCommon.recentListElementToTop(BX.proxy_context.getAttribute('data-userId'));
		}
		if (this.isMobile() || !this.BXIM.messenger.chatList)
		{
			this.BXIM.messenger.popupContactListSearchInput.value = '';
			this.BXIM.messenger.contactListSearchText = '';
			BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = true;
			this.BXIM.messenger.linesList = false;
			this.BXIM.messenger.contactList = false;
			this.BXIM.messenger.contactListShowed = {};
			this.BXIM.messenger.realSearch = !this.BXIM.options.contactListLoad;

			this.userListRedraw();
		}
		if (this.isMobile())
		{
			this.BXIM.messenger.openMessenger(BX.proxy_context.getAttribute('data-userId'), BX.proxy_context);
		}
		else
		{
			this.BXIM.messenger.openMessenger(BX.proxy_context.getAttribute('data-userId'));
		}
	}

	MessengerCommon.prototype.contactListGetFromServer = function(onSuccess)
	{
		if (this.BXIM.messenger.contactListLoad)
			return false;

		if(!BX.type.isFunction(onSuccess))
			onSuccess = BX.DoNothing;

		this.BXIM.messenger.contactListLoad = true;
		BX.ajax({
			url: this.BXIM.pathToAjax+'?CONTACT_LIST&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_CONTACT_LIST' : 'Y', 'IM_AJAX_CALL' : 'Y', 'DESKTOP' : (this.isDesktop()? 'Y': 'N'), 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}

					for (var i in data.GROUPS)
						this.BXIM.messenger.groups[i] = data.GROUPS[i];

					for (var i in data.CHATS)
					{
						if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
							data.CHATS[i].fake = true;
						else if (!this.BXIM.messenger.chat[i])
							data.CHATS[i].fake = true;

						data.CHATS[i].date_create = new Date(data.CHATS[i].date_create);
						this.BXIM.messenger.chat[i] = data.CHATS[i];
					}
					for (var i in data.PHONES)
					{
						this.BXIM.messenger.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}

					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}

					this.userListRedraw();

					if (!this.isMobile())
					{
						this.BXIM.messenger.dialogStatusRedraw();

						if (this.BXIM.messenger.popupChatDialogContactListElements != null)
						{
							this.contactListPrepareSearch('popupChatDialogContactListElements', this.BXIM.messenger.popupChatDialogContactListElements, this.BXIM.messenger.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': this.BXIM.messenger.popupChatDialogContactListElementsType == 'MENTION'});
						}
						if (this.BXIM.messenger.popupTransferDialogContactListElements != null)
						{
							this.contactListPrepareSearch('popupTransferDialogContactListElements', this.BXIM.messenger.popupTransferDialogContactListElements, this.BXIM.messenger.popupTransferDialogContactListSearch.value, {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewBot': false, 'viewTransferOlQueue': true, 'viewOnlyIntranet': true, 'viewOfflineWithPhones': false});
						}
					}

					onSuccess();
				}
				else
				{
					this.BXIM.messenger.contactListLoad = false;
					if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(this.contactListGetFromServer, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.isDesktop() || this.isMobile())
						{
							setTimeout(BX.delegate(this.contactListGetFromServer, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.sendAjaxTry = 0;
				this.BXIM.messenger.contactListLoad = false;
			}, this)
		});
	};

	MessengerCommon.prototype.contactListRealSearch = function(text, callback)
	{
		if (!this.BXIM.messenger.realSearch)
			return false;

		this.contactListRealSearchText = text;
		clearTimeout(this.BXIM.messenger.contactListSearchTimeout);
		this.BXIM.messenger.contactListSearchTimeout = setTimeout(BX.delegate(function(){
			if (this.contactListRealSearchText.length < 3)
			{
				this.BXIM.messenger.realSearchFound = true;
				return false;
			}

			BX.ajax({
				url: this.BXIM.pathToAjax+'?CONTACT_LIST_SEARCH&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_CONTACT_LIST_SEARCH' : 'Y', 'SEARCH' : this.contactListRealSearchText, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){

					this.BXIM.messenger.realSearchFound = true;

					this.BXIM.messenger.userInGroup['search'] = {'id':'search', 'users': []};

					for (var i in data.USERS)
					{
						if (this.BXIM.messenger.users[i])
						{
							continue;
						}

						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
						this.BXIM.messenger.userInGroup['search']['users'].push(i);

						if (data.USERS[i].bot && data.USERS[i].network)
						{
							this.BXIM.messenger.bot[i] = {'type': 'network'};
							this.BXIM.messenger.users[i].extranet = false;
						}
					}

					if (typeof(callback) != 'undefined')
					{
						callback()
					}
					else if (this.BXIM.messenger.contactList)
					{
						this.contactListRedraw({FORCE: true});
					}
				}, this),
				onfailure: BX.delegate(function()	{
					this.BXIM.messenger.realSearchFound = true;
				}, this)
			});
		}, this), 1500);

	}

	MessengerCommon.prototype.contactListSearchClear = function(e)
	{
		if (!this.BXIM.messenger.popupContactListSearchInput)
			return;

		clearTimeout(this.BXIM.messenger.contactListSearchTimeout);
		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

		this.BXIM.messenger.realSearch = !this.BXIM.options.contactListLoad;
		this.BXIM.messenger.realSearchFound = true;

		if (BX.MessengerProxy && this.BXIM.newSearchEnabled)
		{
			BX.MessengerProxy.sendCloseSearchEvent();
			if (BX.MessengerWindow && BX.MessengerWindow.currentTab === 'im-ol')
			{
				this.BXIM.messenger.hideNewRecent();
			}
		}
		this.BXIM.messenger.popupContactListSearchInput.value = '';
		this.BXIM.messenger.contactListSearchText = BX.util.trim(this.BXIM.messenger.popupContactListSearchInput.value);
		BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);

		BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
		BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
		this.BXIM.messenger.popupContactListActive = false;
		this.BXIM.messenger.popupContactListHovered = false;
		clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

		this.BXIM.messenger.chatList = false;
		this.BXIM.messenger.recentList = true;
		this.BXIM.messenger.linesList = false;
		this.BXIM.messenger.contactList = false;
		this.BXIM.messenger.contactListShowed = {};

		this.BXIM.messenger.userInGroup['search'] = {'id':'search', 'users': []};

		this.userListRedraw();
	}

	MessengerCommon.prototype.contactListSearch = function(event)
	{
		if (event.keyCode == 16 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 91) // 224, 17
			return false;

		if (event.keyCode == 37 || event.keyCode == 39)
			return true;

		if (this.BXIM.messenger.popupContactListSearchInput.value != this.BXIM.messenger.contactListSearchLastText || this.BXIM.messenger.popupContactListSearchInput.value  == '')
		{
		}
		else if (event.keyCode == 224 || event.keyCode == 18 || event.keyCode == 17)
		{
			return true;
		}

		if (event.keyCode == 38 || event.keyCode == 40)
		{
			// todo up/down select
			return true;
		}

		if (this.isMobile())
		{
			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.linesList = false;
			this.BXIM.messenger.contactList = true;

			if (!app.enableInVersion(10))
			{
				setTimeout(function(){
					document.body.scrollTop = 0;
				}, 100);
			}
		}
		else
		{
			if (event.keyCode == 27) //Esc
			{
				if (BX.MessengerProxy && this.BXIM.newSearchEnabled)
				{
					BX.MessengerProxy.sendCloseSearchEvent();
				}

				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}

				if (this.BXIM.messenger.contactListSearchText <= 0 && !this.BXIM.messenger.chatList)
				{
					this.BXIM.messenger.popupContactListSearchInput.value = "";
					if (!this.isMobile() && this.BXIM.messenger.popupMessenger && !this.BXIM.messenger.desktop.ready())
					{
						this.BXIM.messenger.popupMessenger.destroy();
						return true;
					}
				}
				else
				{
					this.contactListSearchClear();
					this.BXIM.messenger.popupMessengerTextarea.focus();
					return true;
				}
			}

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.linesList = false;
			this.BXIM.messenger.contactList = true;
		}

		if (
			BX.MessengerProxy
			&& this.BXIM.newSearchEnabled
			&& event.keyCode === 13 //enter
		)
		{
			BX.MessengerProxy.sendUpdateSearchEvent(this.BXIM.messenger.popupContactListSearchInput.value, event.keyCode);
			this.BXIM.messenger.showNewRecent();
		}

		if (this.BXIM.messenger.popupContactListSearchInput.value == this.BXIM.messenger.contactListSearchLastText)
		{
			return true;
		}
		this.BXIM.messenger.contactListSearchText = BX.util.trim(this.BXIM.messenger.popupContactListSearchInput.value);
		this.BXIM.messenger.contactListSearchLastText = this.BXIM.messenger.contactListSearchText;

		if (this.BXIM.messenger.realSearch)
		{
			this.BXIM.messenger.realSearchFound = this.BXIM.messenger.contactListSearchText.length < 3;
		}

		if (!this.isMobile())
		{
			BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);
		}

		if (this.BXIM.messenger.contactListSearchText == '')
		{
			if (this.BXIM.messenger.realSearch)
			{
				this.BXIM.messenger.realSearchFound = true;
				this.BXIM.messenger.realSearch = !this.BXIM.options.contactListLoad;
			}

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = true;
			this.BXIM.messenger.linesList = false;
			this.BXIM.messenger.contactList = false;

			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
			this.BXIM.messenger.popupContactListActive = false;
			this.BXIM.messenger.popupContactListHovered = false;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
		}
		else
		{
			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active');
			this.BXIM.messenger.popupContactListActive = true;
			this.BXIM.messenger.popupContactListHovered = true;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
		}
		if (!BX.MessengerWindow || !BX.MessengerProxy || !this.BXIM.newSearchEnabled || BX.MessengerWindow.currentTab == 'im-ol')
		{
			this.userListRedraw();
		}
	};

	MessengerCommon.prototype.handleInputEvent = function(event)
	{
		if (BX.MessengerProxy && this.BXIM.newSearchEnabled)
		{
			BX.MessengerProxy.sendUpdateSearchEvent(this.BXIM.messenger.popupContactListSearchInput.value, event.keyCode);
			this.BXIM.messenger.showNewRecent();
		}
	};


	/* Section: Recent list */
	MessengerCommon.prototype.recentListRedraw = function(params)
	{
		if (this.debug())
		{
			console.warn('---------------')
			console.time('recentList draw');
		}
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.MobileActionNotEqual('RECENT'))
		{
			return false;
		}

		if (this.BXIM.messenger.recentList && this.BXIM.messenger.popupMessenger)
		{
			if (!this.isMobile())
			{
				if (this.BXIM.messenger.popupMessenger == null)
					return false;

				this.BXIM.messenger.chatList = false;
				this.BXIM.messenger.contactList = false;
				this.BXIM.messenger.recentList = true;
				this.BXIM.messenger.linesList = this.isPage() && BX.MessengerWindow.currentTab == 'im-ol' || this.BXIM.options.openLinesRecent;
			}

			if (this.BXIM.messenger.popupContactListActive)
			{
				BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
				BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
				this.BXIM.messenger.popupContactListActive = false;
				this.BXIM.messenger.popupContactListHovered = false;
				clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
			}

			if (this.BXIM.messenger.contactListSearchText == null || this.BXIM.messenger.contactListSearchText.length > 0)
			{
				this.BXIM.messenger.contactListSearchText = '';
				this.BXIM.messenger.popupContactListSearchInput.value = '';
			}

			if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
				clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

			if (!this.isMobile() && this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
			{
				this.BXIM.messenger.popupPopupMenu.close();
			}

			if (this.debug())
			{
				console.time('recentList checkSum');
			}

			var newRecentList = null;
			if (this.isPage() && BX.MessengerWindow.currentTab == 'im-ol' || this.BXIM.options.openLinesRecent)
			{
				if (!this.BXIM.messenger.linesListLoad)
				{
					this.BXIM.messenger.linesGetList();
				}
				BX.addClass(this.BXIM.messenger.popupContactListElementsWrap, 'bx-messenger-recent-lines-wrap');
				BX.addClass(this.BXIM.messenger.popupContactListElements, 'bx-messenger-recent-lines-container');
				BX.removeClass(this.BXIM.messenger.popupContactListElements, 'bx-messenger-recent-container');
				newRecentList = this.linesListPrepare(params);
			}
			else if (this.isPage() && BX.MessengerWindow.currentTab == 'im')
			{
				BX.addClass(this.BXIM.messenger.popupContactListElements, 'bx-messenger-recent-container');
				BX.removeClass(this.BXIM.messenger.popupContactListElements, 'bx-messenger-recent-lines-container');
				newRecentList = this.recentListPrepare(params);
			}

			if (this.debug())
			{
				console.timeEnd('recentList checkSum');
			}

			if (this.isPage() && BX.MessengerWindow.currentTab == 'im-ol' || this.BXIM.options.openLinesRecent)
			{
				var checkSumCurrent = this.getRecentListCheckSum(this.BXIM.messenger.popupContactListElementsWrap);
				var checkSumNew = this.getRecentListCheckSum(newRecentList);

				if (BX.browser.IsIE11() || checkSumNew != checkSumCurrent)
				{
					this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
					this.BXIM.messenger.popupContactListElementsWrap.appendChild(newRecentList);
				}

				this.BXIM.messenger.hideNewRecent();
				return;
			}
			else
			{
				this.BXIM.messenger.showNewRecent();
			}

			if (this.debug())
			{
				console.log('recentList update', 'done');
			}
		}

		if (this.debug())
		{
			console.timeEnd('recentList draw');
		}
	};

	MessengerCommon.prototype.debug = function(active)
	{
		if (typeof active === 'undefined')
		{
			return BX.localStorage.get('im-debug') == 1;
		}

		BX.localStorage.set('im-debug', active? 1: 0, 86400);
	}

	MessengerCommon.prototype.getRecentListCheckSum = function(node, deep)
	{
		deep = (deep || 0) + 1;

		var result = '';
		var element = null;

		for (var index in node.children)
		{
			if (!node.children.hasOwnProperty(index))
			{
				continue;
			}

			element = node.children[index];

			if (deep == 1)
			{
				result += element.textContent;
			}

			if (element.classList.contains('bx-messenger-cl-avatar-img'))
			{
				result += element.style.background;
			}

			result += element.className;
			result += this.getRecentListCheckSum(element, deep);
		}

		if (deep == 1)
		{
			result = BX.md5(result);
		}

		return result;
	}

	MessengerCommon.prototype.recentListPrepare = function(params)
	{
		params = typeof(params) == 'object'? params: {};

		var recentList = document.createDocumentFragment();

		var list = {
			'pinned': {name: BX.message('IM_RECENT_PINNED'), elements: []},
			'general': {name: '', elements: []},
		};

		this.BXIM.messenger.recent.forEach(function(element)
		{
			if (element.type === 'chat')
			{
				if (element.lines && this.isLinesOperator())
				{
					return true;
				}
			}
			else if (params.showOnlyChat)
			{
				return true;
			}

			if (element.pinned)
			{
				list.pinned.elements.push(element);
			}
			else
			{
				list.general.elements.push(element);
			}

			return true;
		}.bind(this));

		list.pinned.elements.sort(function(a, b)
		{
			return b.message.date.getTime() - a.message.date.getTime();
		});
		list.general.elements.sort(function(a, b)
		{
			return b.message.date.getTime() - a.message.date.getTime();
		});

		var groupCall = false
		if (BX.MessengerCalls)
		{
			BX.MessengerCalls.get().forEach(function(call)
			{
				if (!groupCall)
				{
					groupCall = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group bx-messenger-recent-group-calls"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RECENT_CALLS')})
					]}));
				}

				var node = BX.MessengerCalls.drawElement(call);
				if (node)
				{
					recentList.appendChild(node);
				}
			});
		}

		['pinned', 'general'].forEach(function(type)
		{
			if (list[type].elements.length <= 0)
			{
				return true;
			}

			if (list[type].name)
			{
				recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group bx-messenger-recent-group-"+type}, children : [
					BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, text: list[type].name})
				]}));
			}

			var groups = {};
			list[type].elements.forEach(function(item)
			{
				var entity = {};
				if (item.type === 'user')
				{
					entity = this.BXIM.messenger.users[item.id];
					if (!entity || !entity.active && item.counter == 0)
					{
						return true;
					}
				}
				else if (item.type === 'chat')
				{
					entity = this.BXIM.messenger.chat[item.id.substr(4)];
				}

				if (!entity || typeof entity.name == 'undefined')
				{
					return true;
				}

				if (type !== 'pinned')
				{
					item.dateFormatted = this.formatDate(item.message.date, this.getDateFormatType('RECENT_TITLE'));
					if (!groups[item.dateFormatted])
					{
						groups[item.dateFormatted] = true;
						recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
							BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, text : item.dateFormatted})
						]}));
					}
				}

				var node = this.drawContactListElement({
					'id': item.id,
					'data' : entity,
					'lines': item.lines,
					'counter' : item.counter,
					'invited' : item.invited,
					'message' : item.message,
					'pinned' : item.pinned,
					'unread' : item.unread
				});
				if (node)
				{
					recentList.appendChild(node);
				}
			}.bind(this));
		}.bind(this));

		if (recentList.childNodes.length <= 0)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
		}
		else if (this.BXIM.messenger.recentLoadMore)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				children : [
					BX.create('div', {props : { className: "bx-messenger-content-item-progress"}}),
					BX.create('span', {props : { className: "bx-messenger-cl-item-load-text"}, text: BX.message('IM_CL_LOAD')}),
				]
			}));
		}

		return recentList;
	}

	MessengerCommon.prototype.linesListPrepare = function()
	{
		var recentList = document.createDocumentFragment();

		var list = {
			'new': {name: BX.message('IM_OL_SECTION_NEW'), elements: []},
			'work': {name: BX.message('IM_OL_SECTION_WORK'), elements: []},
			'answered': {name: BX.message('IM_OL_SECTION_ANSWERED'), elements: []},
		};

		this.BXIM.messenger.recent.filter(function(element) {
			return element.lines;
		}).forEach(function (element) {
			if (element.lines.status < 10)
			{
				list.new.elements.push(element);
			}
			else if (element.lines.status < 40)
			{
				list.work.elements.push(element);
			}
			else
			{
				list.answered.elements.push(element);
			}
		});

		// TODO: session priority
		//this.BXIM.messenger.openlines.queue[i].priority

		list.new.elements.sort(function(a, b) {
			return a.lines.id - b.lines.id;
		});
		list.work.elements.sort(function(a, b) {
			return a.lines.id - b.lines.id;
		});
		list.answered.elements.sort(function(a, b) {
			return b.message.date - a.message.date;
		});

		['new', 'work', 'answered'].forEach(function(type)
		{
			if (list[type].elements.length <= 0)
			{
				return true;
			}

			recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
				BX.create("span", {props : { className: "bx-messenger-recent-group-title bx-messenger-recent-category-title bx-messenger-recent-category-title-"+type}, text: list[type].name})
			]}));

			var groups = {};
			list[type].elements.forEach(function(item)
			{
				var entity = this.BXIM.messenger.chat[item.id.substr(4)];
				if (!entity || typeof entity.name == 'undefined')
				{
					return true;
				}

				var groupTitleDate = type === 'answered'? item.message.date: item.lines.date_create;
				if (!groupTitleDate)
				{
					console.error('Date create is not found', item);
				}

				item.dateFormatted = this.formatDate(groupTitleDate, this.getDateFormatType('RECENT_TITLE'));
				if (!groups[item.dateFormatted])
				{
					groups[item.dateFormatted] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, text : item.dateFormatted})
					]}));
				}

				var node = this.drawContactListElement({
					'id': item.id,
					'data' : entity,
					'lines' : item.lines,
					'counter' : item.counter,
					'invited' : item.invited,
					'message' : item.message,
					'pinned' : item.pinned
				});
				if (node)
				{
					recentList.appendChild(node);
				}
			}.bind(this));
		}.bind(this));

		if (this.BXIM.messenger.linesListLoad && recentList.childNodes.length <= 0)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_EMPTY_OL_TEXT_2')
			}));
		}
		else if (!this.BXIM.messenger.linesListLoad)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				children : [
					BX.create('div', {props : { className: "bx-messenger-content-item-progress"}}),
					BX.create('span', {props : { className: "bx-messenger-cl-item-load-text"}, text: BX.message('IM_CL_LOAD')}),
				]
			}));
		}

		return recentList;
	};

	MessengerCommon.prototype.recentListGetItem = function(dialogId)
	{
		return this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});
	}

	MessengerCommon.prototype.recentListAddItem = function(params)
	{
		if (this.isMobile() || !params.id)
		{
			return false;
		}

		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == params.id;
		});

		if (element)
		{
			if (!params.date_update)
			{
				params.date_update = new Date();
			}
			BX.util.objectMerge(element, params);
		}
		else
		{
			if (!params.title)
			{
				var entity = this.getUserParam(params.id);
				if (entity)
				{
					params.title = entity.name;
				}
			}

			var defaultValue = {
				id: 0,
				chat_id: 0,
				counter: 0,
				date_update: new Date(),
				message: {id: 0, text: undefined, date: new Date(), author_id: 0, status: 'delivered', attach: false, file: false},
				options: [],
				pinned: false,
				invited: false,
				title: '',
				type: params.id.toString().substr(0, 4) === 'chat'? 'chat': 'user',
				unread: false
			};

			if (
				typeof params.chat_id === 'undefined'
				&& params.id.toString().startsWith('chat')
			)
			{
				params.chat_id = parseInt(params.id.toString().substr(4));
			}

			this.BXIM.messenger.recent.unshift(
				BX.util.objectMerge(defaultValue, params)
			);
		}

		return true;
	}

	MessengerCommon.prototype.recentListUpdateItem = function(params)
	{
		if (this.isMobile() || !params.id)
		{
			return false;
		}

		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == params.id;
		});

		if (element)
		{
			if (!params.date_update)
			{
				params.date_update = new Date();
			}
			BX.util.objectMerge(element, params);
		}
	}

	MessengerCommon.prototype.inRecentList = function(dialogId)
	{
		if (!dialogId)
		{
			return false;
		}

		return !!this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});
	};

	MessengerCommon.prototype.recentListHide = function(dialogId, sendAjax)
	{
		if (!dialogId)
			return false;

		this.BXIM.messenger.recent = this.BXIM.messenger.recent.filter(function(element){
			return element.id != dialogId;
		});

		if (this.BXIM.messenger.recentList)
		{
			this.recentListRedraw();
		}

		if (!this.isMobile())
		{
			BX.localStorage.set('mrlr', dialogId, 5);
		}

		if (this.BXIM.messenger.birthdayRecent[dialogId])
		{
			BX.localStorage.set('mbdh-'+dialogId, true, 86400);
			delete this.BXIM.messenger.birthdayRecent[dialogId];
		}

		sendAjax = sendAjax != false;

		if (sendAjax)
		{
			if (BX.MessengerProxy)
			{
				BX.MessengerProxy.sendHideChatEvent(dialogId);
			}
			BX.ajax({
				url: this.BXIM.pathToAjax+'?RECENT_HIDE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_RECENT_HIDE' : 'Y', 'DIALOG_ID' : dialogId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}

		this.readMessage(dialogId, sendAjax, false);

		if (dialogId.toString().substr(0, 4) == 'chat')
		{
			if (this.isMobile())
			{
				app.onCustomEvent('onPullClearWatch', {'id': 'IM_PUBLIC_'+dialogId.substr(4)});
			}
			else
			{
				BX.PULL.clearWatch('IM_PUBLIC_'+dialogId.substr(4));
			}
		}

		delete this.BXIM.messenger.showMessage[dialogId];
		delete this.BXIM.messenger.history[dialogId];

		if (this.BXIM.messenger.currentTab == dialogId)
		{
			this.BXIM.messenger.currentTab = 0;
			this.BXIM.messenger.extraOpen(
				BX.create("div", { attrs : { style : "padding-top: 300px"}, props : { className : "bx-messenger-box-empty" }, html: BX.message('IM_M_EMPTY')})
			);
		}
	};

	MessengerCommon.prototype.recentListElementUpdate = function(dialogId, messageId, messageText, chatCounter, isMuted)
	{
		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});

		if (!element)
		{
			return false;
		}

		if (element.message.id != messageId)
		{
			return false;
		}

		element.message.text = messageText;

		if (typeof chatCounter !== 'undefined')
		{
			element.counter = chatCounter;

			if (element.lines)
			{
				this.BXIM.linesDetailCounter[element.id] = isMuted? 0: chatCounter;
			}
			else
			{
				this.BXIM.dialogDetailCounter[element.id] = isMuted? 0: chatCounter;
			}

			this.BXIM.messenger.updateMessageCount();
		}

		return true;
	}

	MessengerCommon.prototype.recentListElementToTop = function(dialogId)
	{
		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});

		if (element)
		{
			element.message.date = new Date();
		}
		else
		{
			var entity = this.getUserParam(dialogId);
			if (!entity)
			{
				return false;
			}

			this.recentListAddItem({
				id: dialogId,
				title: entity.name,
			});
		}

		if (this.BXIM.messenger.recentList || BX.MessengerExternalList && BX.MessengerExternalList.isAvailable())
		{
			this.recentListRedraw();
		}

		if (!this.isMobile())
		{
			BX.localStorage.set('mrlr', dialogId, 5);
		}
	};

	MessengerCommon.prototype.recentListElementPin = function(dialogId, active)
	{
		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});
		if (!element)
		{
			return true;
		}

		if (element.pinned == active)
		{
			return true;
		}

		element.pinned = !!active;

		this.recentListRedraw();

		return true;
	};

	MessengerCommon.prototype.recentListElementStatusChange = function(dialogId, status)
	{
		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});

		if (!element || !element.message)
		{
			return true;
		}

		if (element.message.status == status)
		{
			return true;
		}

		element.message.status = status;

		this.recentListRedraw();

		return true;
	};

	MessengerCommon.prototype.recentListElementStatusError = function(dialogId, messageId)
	{
		var element = this.BXIM.messenger.recent.find(function(element) {
			return element.id == dialogId;
		});

		if (!element || !element.message)
		{
			return true;
		}

		if (element.message.status == 'error')
		{
			return true;
		}

		if (element.message.id != messageId)
		{
			return true;
		}

		element.message.status = 'error';

		this.recentListRedraw();

		return true;
	};

	MessengerCommon.prototype.recentListElementFormat = function(element)
	{
		element.date_update = new Date(element.date_update);

		if(typeof element.lines !== 'undefined')
		{
			element.lines.date_create = new Date(element.lines.date_create);
		}

		if(typeof element.message !== 'undefined')
		{
			element.message.text = BX.util.htmlspecialchars(element.message.text);
			element.message.date = new Date(element.message.date);
		}

		if(typeof element.user !== 'undefined')
		{
			if (element.user.id > 0)
			{
				element.user.name = BX.util.htmlspecialchars(element.user.name);
				element.user.first_name = BX.util.htmlspecialchars(element.user.first_name);
				element.user.last_name = BX.util.htmlspecialchars(element.user.last_name);
				element.user.work_position = BX.util.htmlspecialchars(element.user.work_position);
				element.user.external_auth_id = BX.util.htmlspecialchars(element.user.external_auth_id);
				element.user.status = BX.util.htmlspecialchars(element.user.status);
				element.user.absent = element.user.absent? new Date(element.user.absent): false;
				element.user.idle = element.user.idle? new Date(element.user.idle): false;
				element.user.last_activity_date = element.user.last_activity_date? new Date(element.user.last_activity_date): false;
				element.user.mobile_last_date = element.user.mobile_last_date? new Date(element.user.mobile_last_date): false;
				element.user.profile = this.BXIM.path.profileTemplate.replace('#user_id#', element.user.id);

				this.BXIM.messenger.users[element.user.id] = element.user;
			}
			delete element.user;
		}

		if(typeof element.chat !== 'undefined')
		{
			if (element.chat.id > 0)
			{
				element.chat.name = BX.util.htmlspecialchars(element.chat.name);
				element.chat.entity_data_1 = BX.util.htmlspecialchars(element.chat.entity_data_1);
				element.chat.entity_data_2 = BX.util.htmlspecialchars(element.chat.entity_data_2);
				element.chat.entity_data_3 = BX.util.htmlspecialchars(element.chat.entity_data_3);
				element.chat.entity_id = BX.util.htmlspecialchars(element.chat.entity_id);
				element.chat.entity_type = BX.util.htmlspecialchars(element.chat.entity_type);
				element.chat.date_create = new Date(element.chat.date_create);

				this.BXIM.messenger.chat[element.chat.id] = element.chat;
			}
			delete element.chat;
		}

		delete element.avatar;

		return element;
	};

	MessengerCommon.prototype.recentListApply = function(list, counters)
	{
		this.BXIM.messenger.recentLoadMore = !!list.hasMore;
		this.BXIM.messenger.recentLastMessageUpdateDate = list.items.length > 0? list.items.slice(-1)[0].message.date: '';

		this.BXIM.messenger.recent = list.items.filter(function(element)
		{
			if (element.id === 'notify')
			{
				return false;
			}

			element = this.recentListElementFormat(element);

			return true;
		}.bind(this));

		if (counters)
		{
			this.recentListCounterApply(counters);
		}

		this.recentListBirthdayApply();

		this.BXIM.messenger.updateMessageCount();
	};

	MessengerCommon.prototype.recentListUpdate = function(list, counters, redrawType)
	{
		redrawType = redrawType || 'update';

		var redrawTab = false;
		if (list.length > 0)
		{
			var updateElement = {};
			list.forEach(function(element) {
				this.BXIM.messenger.redrawTab[element.id] = true;
				if (
					this.BXIM.messenger.showMessage[element.id]
					&& this.BXIM.messenger.showMessage[element.id].length > 30
				)
				{
					this.BXIM.messenger.showMessage[element.id] = this.BXIM.messenger.showMessage[element.id].slice(-30);
				}
				element = this.recentListElementFormat(element);
				updateElement[element.id] = element.date_update;
			}.bind(this));

			this.BXIM.messenger.recent = this.BXIM.messenger.recent.filter(function(element)
			{
				if (
					typeof updateElement[element.id] !== 'undefined'
					&& updateElement[element.id] > element.date_update
					&& this.BXIM.messenger.currentTab == element.id
				)
				{
					redrawTab = true;
				}

				return typeof updateElement[element.id] === 'undefined';
			}.bind(this)).concat(list);
		}

		if (counters)
		{
			this.recentListCounterApply(counters);
		}

		this.recentListBirthdayApply();

		if (this.BXIM.dialogOpen && redrawTab)
		{
			if (redrawType === 'close')
			{
				this.BXIM.messenger.currentTab = 0;
				this.BXIM.messenger.openChatFlag = false;
				this.BXIM.messenger.openCallFlag = false;
				this.BXIM.messenger.openLinesFlag = false;
				this.BXIM.messenger.extraClose();
			}
			else
			{
				this.BXIM.messenger.openMessenger();
			}
		}

		this.BXIM.messenger.updateMessageCount();
	};

	MessengerCommon.prototype.recentListCounterApply = function(counters)
	{
		this.BXIM.dialogDetailCounter = counters.dialog;

		if (counters.dialogUnread)
		{
			counters.dialogUnread.forEach(function(dialogId)
			{
				this.BXIM.dialogDetailCounter[dialogId] = 1;
			}.bind(this));
		}

		if (counters.chatUnread)
		{
			counters.chatUnread.forEach(function(chatId)
			{
				this.BXIM.dialogDetailCounter['chat'+chatId] = 1;
			}.bind(this));
		}

		for (var chatId in counters.chat)
		{
			if (counters.chat.hasOwnProperty(chatId))
			{
				this.BXIM.dialogDetailCounter['chat'+chatId] = counters.chat[chatId];
			}
		}

		for (var chatId in counters.lines)
		{
			if (counters.lines.hasOwnProperty(chatId))
			{
				this.BXIM.linesDetailCounter['chat'+chatId] = counters.lines[chatId];
			}
		}

		this.BXIM.messenger.recent.forEach(function(element)
		{
			if (element.lines)
			{
				if (typeof this.BXIM.linesDetailCounter[element.id] !== 'undefined')
				{
					if (element.counter != this.BXIM.linesDetailCounter[element.id])
					{
						element.counter = this.BXIM.linesDetailCounter[element.id];
					}

					delete this.BXIM.linesDetailCounter[element.id];
				}
			}
			else
			{
				if (typeof this.BXIM.dialogDetailCounter[element.id] !== 'undefined')
				{
					if (element.counter != this.BXIM.dialogDetailCounter[element.id])
					{
						element.counter = this.BXIM.dialogDetailCounter[element.id];
					}

					delete this.BXIM.dialogDetailCounter[element.id];
				}
			}
		}.bind(this));

		this.BXIM.mailCount = counters.type.mail;
		this.BXIM.notifyCount = counters.type.notify;
		this.BXIM.messageCount = counters.type.dialog + counters.type.chat;
		this.BXIM.linesCount = counters.type.lines;
	}

	MessengerCommon.prototype.recentListBirthdayApply = function()
	{
		if (this.BXIM.messenger.birthdayEnable === 'none')
		{
			return false;
		}

		if (!this.BXIM.settings.viewBirthday)
		{
			for (var userId in this.BXIM.messenger.birthdayRecent)
			{
				if (!this.BXIM.messenger.birthdayRecent.hasOwnProperty(userId))
				{
					continue;
				}

				var result = this.BXIM.messenger.birthdayRecent[userId];
				if (result === 'new')
				{
					this.BXIM.messenger.recent = this.BXIM.messenger.recent.filter(function(element){
						return element.id != userId;
					});
				}
				else if (result != 'skip')
				{
					var element = this.BXIM.messenger.recent.find(function(item) {
						return item.id == userId && item.message.id === 'birthday'+userId;
					});
					if (element)
					{
						element.message = this.BXIM.messenger.birthdayRecent[userId];
					}
				}

				if (typeof this.BXIM.messenger.showMessage[userId] !== 'undefined')
				{
					this.BXIM.messenger.showMessage[userId] = this.BXIM.messenger.showMessage[userId].filter(function(element) {
						return !element.toString().startsWith('birthday');
					});
				}

				delete this.BXIM.messenger.birthdayRecent[userId];
			}

			return true;
		}

		if (typeof this.BXIM.messenger.showMessage[userId] !== 'undefined')
		{
			this.BXIM.messenger.showMessage[userId] = this.BXIM.messenger.showMessage[userId].filter(function(element) {
				return !element.toString().startsWith('birthday');
			});
		}

		var today = BX.Main.Date.format('d-m');
		var birthdayList = [];
		var birthdayObject = {};
		for (var userId in this.BXIM.messenger.users)
		{
			if (!this.BXIM.messenger.users.hasOwnProperty(userId))
			{
				continue;
			}

			if (userId == this.BXIM.userId)
			{
				continue;
			}

			if (this.BXIM.messenger.birthdayEnable === 'all')
			{
				if (this.BXIM.messenger.users[userId].birthday === today)
				{
					birthdayList.push(userId);
					birthdayObject[userId] = true;
				}
				else if (this.BXIM.messenger.birthdayUsers[userId])
				{
					birthdayList.push(userId);
					birthdayObject[userId] = true;
				}
			}
			else if (this.BXIM.messenger.birthdayUsers[userId])
			{
				birthdayList.push(userId);
				birthdayObject[userId] = true;
			}
		}
		birthdayList.forEach(function(userId)
		{
			var birthdayDate = BX.MessengerCommon.getNowDate(true);
			var birthdayMessageId = 'birthday'+userId;

			this.BXIM.messenger.message[birthdayMessageId] = {
				'id': birthdayMessageId,
				'senderId': 0,
				'recipientId': userId,
				'date': birthdayDate,
				'text': BX.message('IM_M_BIRTHDAY_MESSAGE').replace('#USER_NAME#', '<span class="bx-messenger-birthday-icon"></span><strong>'+this.BXIM.messenger.users[userId].name+'</strong>'),
				'textOriginal': BX.message('IM_M_BIRTHDAY_MESSAGE').replace('#USER_NAME#', this.BXIM.messenger.users[userId].name)
			};

			if (!this.BXIM.messenger.showMessage[userId])
			{
				this.BXIM.messenger.showMessage[userId] = [birthdayMessageId];
			}
			else
			{
				var element = this.BXIM.messenger.showMessage[userId].find(function(id) {
					return id == birthdayMessageId;
				});
				if (!element)
				{
					this.BXIM.messenger.showMessage[userId].push(birthdayMessageId);
					this.BXIM.messenger.showMessage[userId].sort(function(a, b) {
						return this.BXIM.messenger.message[b].date.getTime() - this.BXIM.messenger.message[a].date.getTime();
					}.bind(this));
				}
			}

			var element = this.BXIM.messenger.recent.find(function(item) {
				return item.id == userId;
			});
			if (element)
			{
				if (element.message.date.getTime() < birthdayDate.getTime())
				{
					this.BXIM.messenger.birthdayRecent[userId] = element.message;

					element.message = {
						id: birthdayMessageId,
						date: birthdayDate,
						author_id: element.id,
						status: 'delivered',
						text: BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT'),
						attach: false,
						file: false,
					};
				}
				else if (element.message.date.getTime() != birthdayDate.getTime())
				{
					this.BXIM.messenger.birthdayRecent[userId] = 'skip';
				}
			}
			else if (!BX.localStorage.get('mbdh-'+userId, true, 86400))
			{
				this.BXIM.messenger.birthdayRecent[userId] = 'new';
				BX.MessengerCommon.recentListAddItem({
					id: userId,
					message: {
						id: birthdayMessageId,
						date: birthdayDate,
						text: BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT'),
					},
				});
			}

		}.bind(this));

		return true;
	}

	MessengerCommon.prototype.recentListGetSortIndex = function()
	{
		var sortIndex = {};
		var tmpIndex = 0;

		this.BXIM.messenger.recent.sort(function(a, b) {
			return b.message.date.getTime() - a.message.date.getTime();
		});

		for (var item = 0; item < this.BXIM.messenger.recent.length; item++)
		{
			tmpIndex =  this.BXIM.messenger.recent.length-item;
			sortIndex[this.BXIM.messenger.recent[item].id] = tmpIndex;
		}

		return sortIndex;
	}

	MessengerCommon.prototype.getCounter = function(dialogId)
	{
		var element = this.recentListGetItem(dialogId);
		if (typeof element !== 'undefined')
		{
			return element.counter;
		}
		else if (
			typeof this.BXIM.dialogDetailCounter !== 'undefined'
			&& typeof this.BXIM.dialogDetailCounter[dialogId] !== 'undefined'
		)
		{
			return this.BXIM.dialogDetailCounter[dialogId];
		}

		return 0;
	};

	MessengerCommon.prototype.getVideoconfLink = function(dialogId)
	{
		if (
			!dialogId
			|| !this.BXIM.messenger.chat[dialogId.substr(4)]
			|| !this.BXIM.messenger.chat[dialogId.substr(4)].public
		)
		{
			return null;
		}

		return this.BXIM.messenger.chat[dialogId.substr(4)].public.link;
	}

	MessengerCommon.prototype.getVideoconfLinkByCode = function(code)
	{
		if (!code)
		{
			return null;
		}

		return location.origin.replace('http://', 'https://')+'/video/'+code;
	};

	MessengerCommon.prototype.drawContactListElement = function(params)
	{
		if (!params || !params.id)
			return null;

		params.userIsChat = params.id.toString().substr(0, 4) == 'chat';
		params.userIsQueue = params.id.toString().substr(0, 5) == 'queue';
		params.userIsStructure = params.id.toString().substr(0, 9) == 'structure';
		params.extraClass = params.extraClass || '';
		params.showUserLastActivityDate = typeof params.showUserLastActivityDate === 'boolean'? params.showUserLastActivityDate: !this.BXIM.messenger.recentList;
		params.showLastMessage = params.showLastMessage === false? false: true;
		params.showCounter = params.showCounter === false? false: true;
		params.data = params.data? params.data: {};
		params.counter = params.counter? params.counter: 0;
		params.unread = params.unread || false;
		params.message = params.message || null;

		if (!params.userIsChat && this.BXIM.userId == params.data.id && params.data.extranet)
		{
			return null;
		}

		var chatStatus = '';
		var newMessage = '';
		var newMessageCount = '';
		var writingMessage = '';

		if (params.showCounter)
		{
			if (params.counter)
			{
				newMessage = 'bx-messenger-cl-status-new-message';
				newMessageCount = '<span class="bx-messenger-cl-count-digit">' + (params.counter < 100? params.counter: '99+') + '</span>';
			}
			else if (params.unread)
			{
				newMessage = 'bx-messenger-cl-status-new-message';
				newMessageCount = '<span class="bx-messenger-cl-count-digit"></span>';
			}
			if (this.countWriting(params.id))
			{
				writingMessage = 'bx-messenger-cl-status-writing';
			}
			if (
				params.userIsChat
				&& this.BXIM.messenger.chat[params.id.substr(4)]
				&& this.BXIM.messenger.chat[params.id.substr(4)].mute_list
				&& this.BXIM.messenger.chat[params.id.substr(4)].mute_list[this.BXIM.userId]
			)
			{
				newMessage += ' bx-messenger-cl-status-muted';
			}
		}

		var avatar = '';
		var color = this.BXIM.messenger.users[this.BXIM.userId].color;

		if (!(params.userIsQueue || params.userIsStructure))
		{
			avatar = params.data.avatar;
			color = params.data.color;
		}

		if (!avatar)
		{
			avatar = this.BXIM.pathToBlankImage;
		}

		var description = '';
		var showCrm = false;
		var descriptionInvited = false;

		var userInvited = (
			!params.userIsChat
			&& params.invited
			&& !params.data.last_activity_date
		);

		if (
			this.BXIM.settings.viewLastMessage
			&& params.showLastMessage
			&& params.id
		)
		{
			if (params.message)
			{
				if (userInvited && !params.message.id)
				{
					description = '<span class="bx-messenger-cl-user-invited">'+BX.message('IM_USER_INVITED')+'</span>';
					descriptionInvited = true;
				}
				else if (params.message.id != 0)
				{
					description = this.purifyText(params.message.text, {WITH_ATTACH: params.message.attach, WITH_FILE: params.message.file});
				}
			}

			if (
				description
				&& params.message
				&& params.message.author_id
				&& params.id != this.BXIM.userId
			)
			{
				if (params.message.author_id == this.BXIM.userId)
				{
					description = '<span class="bx-messenger-cl-user-reply"></span>' + description;
				}
				else if (
					params.userIsChat
					&& this.BXIM.messenger.users[params.message.author_id]
					&& !this.BXIM.messenger.users[params.message.author_id].connector
				)
				{
					var messageUser = this.BXIM.messenger.users[params.message.author_id];
					var avatarClass = '';
					var avatarStyle = '';
					if (this.isBlankAvatar(messageUser.avatar))
					{
						avatarClass = "bx-messenger-cl-user-reply-avatar-default";
					}
					else
					{
						avatarStyle = 'background-image: url(\''+messageUser.avatar+'\')';
					}
					description = '<span class="bx-messenger-cl-user-reply-avatar '+avatarClass+'" title="'+messageUser.name+'" style="'+avatarStyle+'"></span>' + description;
				}
			}

		}
		if (!description)
		{
			if (params.userIsChat)
			{
				if (params.data.type == 'call')
				{
					description = BX.message('IM_CL_PHONE');
				}
				else if (params.data.type == 'lines')
				{
					description = BX.message('IM_CL_LINES');
				}
				else if (params.data.type == 'open')
				{
					description = BX.message('IM_CL_OPEN_CHAT_NEW');
				}
				else
				{
					description = BX.message('IM_CL_CHAT_NEW');
				}
			}
			else if (params.userIsQueue)
			{
				if (params.data.type == 'olQueue')
				{
					description = BX.message('IM_CL_OL_QUEUE');
				}
				else if (params.data.type == 'viQueue')
				{
					description = BX.message('IM_CL_VI_QUEUE');
				}
			}
			else if (params.userIsStructure)
			{
				description = BX.message('IM_CL_STRUCTURE');
			}
			else
			{
				description = this.getUserPosition(this.BXIM.messenger.users[params.id], params.showUserLastActivityDate);
			}
		}

		if (params.userIsChat)
		{
			if (params.data.type == 'lines')
			{
				var session = this.linesGetSession(this.BXIM.messenger.chat[params.id.substr(4)]);
				showCrm = session.crm == 'Y';
				chatStatus += " bx-messenger-cl-avatar-" + this.linesGetSource(this.BXIM.messenger.chat[params.id.substr(4)]);
			}
			else if (params.data.entity_type == 'CRM')
			{
				showCrm = true;
				chatStatus += " bx-messenger-cl-avatar-type-crm";
			}
			else
			{
				chatStatus = " bx-messenger-cl-item-chat-" + params.data.type;
			}
		}
		else if (params.userIsQueue)
		{
			if (params.data.type == 'olQueue')
			{
				chatStatus = " bx-messenger-cl-avatar-lines";
			}
			else if (params.data.type == 'viQueue')
			{
				chatStatus = " bx-messenger-cl-avatar-call";
			}
		}
		else if (params.userIsStructure)
		{
			chatStatus = " bx-messenger-cl-avatar-structure";
		}

		var avatarColor = !userInvited && this.isBlankAvatar(avatar)? color: '';
		var chatHideAvatar = params.userIsChat && avatarColor? 'bx-messenger-cl-avatar-status-hide': '';
		var userName = params.data.name;
		if (!params.userIsChat && !params.userIsQueue && !params.userIsStructure && this.BXIM.userId == params.data.id)
		{
			userName = userName + ' (<b><i>' + BX.message('IM_YOU') + '</i></b>)';
		}

		var classAvatar = '';
		var className = "bx-messenger-cl-item  bx-messenger-cl-id-" + (params.userIsChat? 'chat': '') + (params.userIsQueue? 'queue': '') + params.data.id;
		if (params.userIsChat)
		{
			classAvatar = 'bx-messenger-cl-avatar-' + params.data.type + ' ' + (this.BXIM.messenger.generalChatId == params.data.id? " bx-messenger-cl-item-chat-general": "");
			className += " bx-messenger-cl-item-chat " + newMessage + " " + writingMessage + " " + chatStatus + " " + (this.BXIM.messenger.generalChatId == params.data.id? "bx-messenger-cl-item-chat-general": "");
		}
		else if (params.userIsQueue)
		{
			className += chatStatus;
		}
		else if (params.userIsStructure)
		{
			className += chatStatus;
		}
		else if (userInvited)
		{
			className += " bx-messenger-cl-item-user-invited";
			if (descriptionInvited)
			{
				className += " bx-messenger-cl-item-user-invited-text";
			}
		}
		else
		{
			className += " bx-messenger-cl-avatar-user bx-messenger-cl-status-" + this.getUserStatus(this.BXIM.messenger.users[params.data.id]) + " " + newMessage + " " + writingMessage;
		}
		className += " " + params.extraClass;

		if (
			!newMessageCount
			&& params.message
			&& params.message.status
			&& params.message.author_id == this.BXIM.userId
			&& params.id != this.BXIM.userId
		)
		{
			className += " bx-messenger-cl-item-message-status-"+params.message.status;
		}
		if (!newMessageCount && params.pinned)
		{
			className += " bx-messenger-cl-item-pinned";
		}

		var dataStatus = '';
		if (params.userIsChat)
		{
			if (params.data.type == 'lines' && params.lines)
			{
				dataStatus = params.lines.status;
			}
			else
			{
				dataStatus = params.data.type;
			}
		}
		else
		{
			dataStatus = this.getUserStatus(this.BXIM.messenger.users[params.data.id]);
		}

		var avatarStyle = ''
		if (BX.MessengerCommon.isBlankAvatar(avatar))
		{
			avatarStyle = 'style="background-color: '+avatarColor+'"';
		}
		else
		{
			avatarStyle = 'style="background: url(\''+avatar+'\'); background-size: cover;"';
		}

		return BX.create("span", {
			props : { className: className },
			attrs : { 'data-userId' : params.id, 'data-name' : BX.util.htmlspecialcharsback(params.data.name), 'data-status' : dataStatus, 'data-avatar' : avatar, 'data-userIsChat' : params.userIsChat, 'data-isPinned' : params.pinned, 'data-userIsQueue' : params.userIsQueue },
			html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
					'<span title="'+params.data.name+'" class="bx-messenger-cl-avatar '+classAvatar+' '+chatHideAvatar+'">' +
						'<span class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(avatar)? " bx-messenger-cl-avatar-img-default": "")+'" '+avatarStyle+'></span>' +
						(showCrm? '<span class="bx-messenger-cl-crm"></span>':'') +
						(!params.userIsQueue && !params.userIsStructure? '<span class="bx-messenger-cl-status"></span>':'') +
					'</span>'+
					'<span class="bx-messenger-cl-user">'+
						'<div class="bx-messenger-cl-user-title'+(params.data.extranet && params.data.type != 'lines'? " bx-messenger-user-extranet": "")+'" title="'+params.data.name+'">'+userName+'</div>'+
						'<div class="bx-messenger-cl-user-desc">'+description+'</div>'+
					'</span>'
		});
	}

	/* Section: Chat list */
	MessengerCommon.prototype.chatListRedraw = function(params)
	{
		if (this.MobileActionNotEqual('RECENT') || this.BXIM.messenger.popupMessenger == null)
			return false;

		BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active');
		BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-hover bx-messenger-box-contact-normal');
		this.BXIM.messenger.popupContactListActive = true;
		this.BXIM.messenger.popupContactListHovered = true;
		clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

		if (!this.isMobile())
		{
			if (this.BXIM.messenger.popupMessenger == null)
				return false;
		}

		this.BXIM.messenger.chatList = true;
		this.BXIM.messenger.recentList = false;
		this.BXIM.messenger.linesList = false;
		this.BXIM.messenger.contactList = false;

		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

		if (!this.isMobile() && this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
		{
			this.BXIM.messenger.popupPopupMenu.close();
		}

		this.BXIM.messenger.showNewRecent();
		if (BX.MessengerProxy)
		{
			BX.MessengerProxy.sendOpenSearchEvent(this.BXIM.messenger.contactListSearchText);
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.showImages();
		}
	};

	MessengerCommon.prototype.chatListPrepare = function(params)
	{
		var chatList = document.createDocumentFragment();

		var groups = {};
		params = typeof(params) == 'object'? params: {};

		var listName = typeof(params.listName) != 'undefined'? params.listName: 'contactList';
		var searchText = typeof(params.searchText) != 'undefined'? params.searchText: this.BXIM.messenger.contactListSearchText;
		var activeSearch = !(searchText != null && searchText.length == 0);
		var searchStructureId = activeSearch && searchText.substr(0,9) == 'structure'? searchText.substr(9): 0;
		var searchWaitBackend = this.BXIM.messenger.realSearch && !this.BXIM.messenger.realSearchFound;
		var viewOnlyIntranet =  typeof(params.viewOnlyIntranet) != 'undefined'? params.viewOnlyIntranet: false;
		var extraEnable =  typeof(params.extra) != 'undefined'? params.extra: true;
		var viewOffline =  typeof(params.viewOffline) != 'undefined'? params.viewOffline: activeSearch /* || !this.BXIM.settings? true: this.BXIM.settings.viewOffline*/;
		var viewOfflineWithPhones =  typeof(params.viewOfflineWithPhones) != 'undefined'? params.viewOfflineWithPhones: false;
		var viewChat =  typeof(params.viewChat) != 'undefined'? params.viewChat: true;
		var viewOpenChat =  typeof(params.viewOpenChat) != 'undefined'? params.viewOpenChat: true;
		var viewSelf =  typeof(params.viewSelf) != 'undefined'? params.viewSelf: true;
		var viewTransferViQueue =  typeof(params.viewTransferViQueue) != 'undefined'? params.viewTransferViQueue: false;
		var viewTransferOlQueue =  typeof(params.viewTransferOlQueue) != 'undefined'? params.viewTransferOlQueue: false;
		var viewBot =  typeof(params.viewBot) != 'undefined'? params.viewBot: true;
		var callback =  typeof(params.callback) != 'undefined'? params.callback: {};
		var showBitrix24Search = activeSearch && searchText.length >= 3 && this.BXIM.messenger.realSearchAvailable && !this.BXIM.messenger.realSearch && !viewOnlyIntranet;
		var showStructureBlock = listName == 'contactList' || listName == 'popupChatDialogContactListElements' && (this.BXIM.messenger.popupChatDialogContactListElementsType == 'CHAT_ADD' || this.BXIM.messenger.popupChatDialogContactListElementsType == 'CHAT_EXTEND' || this.BXIM.messenger.popupChatDialogContactListElementsType == 'CHAT_CREATE' && this.BXIM.messenger.chatCreateType != 'private');
		var showStructureSonetBlock = listName == 'contactList';
		var showUserLastActivityDate = typeof params.showUserLastActivityDate === 'boolean'? params.showUserLastActivityDate: !this.BXIM.messenger.recentList;

		if (typeof(callback.empty) != 'function')
		{
			callback.empty = function(){}
		}

		if (!this.BXIM.messenger.contactListLoad)
		{
			chatList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				children : [
					BX.create('div', {props : { className: "bx-messenger-content-item-progress"}}),
					BX.create('span', {props : { className: "bx-messenger-cl-item-load-text"}, text: BX.message('IM_CL_LOAD')}),
				]
			}));

			this.contactListGetFromServer();
			return chatList;
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		var contactListSize = this.BXIM.messenger.popupContactListElementsSize;
		var elementSize = 46;
		var categorySize = 29;
		var moreSize = 26;
		var categoryCount = 0;
		var minElementPerCategory = activeSearch? 5: 3;

		var category = [];
		if (viewTransferOlQueue)
		{
			category.push({'id': 'olQueue', 'name': BX.message('IM_CTL_CHAT_OL_QUEUE'), 'title': '', 'more': BX.message('IM_CL_MORE_QUEUE')});
		}
		else if (viewTransferViQueue)
		{
			category.push({'id': 'viQueue', 'name': BX.message('IM_CTL_CHAT_VI_QUEUE'), 'title': '', 'more': BX.message('IM_CL_MORE_QUEUE')});
		}

		var userListObject = this.BXIM.messenger.users;
		if (activeSearch && searchStructureId)
		{
			category.push({'id': 'private', 'name': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'title': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'more': BX.message('IM_CL_MORE_PRIVATE_NEW_MSGVER_1')});

			userListObject = {};
			if (this.BXIM.messenger.userInGroup[searchStructureId])
			{
				for (var i = 0; i < this.BXIM.messenger.userInGroup[searchStructureId].users.length; i++)
				{
					userListObject[this.BXIM.messenger.userInGroup[searchStructureId].users[i]] = this.BXIM.messenger.users[this.BXIM.messenger.userInGroup[searchStructureId].users[i]];
				}
			}
		}
		else if (activeSearch)
		{
			category.push({'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE_NEW_MSGVER_1'), 'title': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'more': BX.message('IM_CL_MORE_PRIVATE_NEW_MSGVER_1')});
			category.push({'id': 'bot', 'name': BX.message('IM_CTL_CHAT_BOT'), 'title': '', 'more': BX.message('IM_CL_MORE_BOT')});
			category.push({'id': 'open', 'name': BX.message('IM_CTL_CHAT_OPEN_NEW'), 'title': BX.message('IM_CL_CREATE_OPEN_NEW'), 'more': BX.message('IM_CL_MORE_OPEN_NEW'), skip: !this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet});
			category.push({'id': 'chat', 'name': BX.message('IM_CTL_CHAT_CHAT_NEW'), 'title': BX.message('IM_CL_CREATE_CHAT_NEW'), 'more': BX.message('IM_CL_MORE_CHAT_NEW')});
			category.push({'id': 'lines', 'name': BX.message('IM_CTL_CHAT_LINES'), 'title': '', 'more': BX.message('IM_CL_MORE_LINES')});
			category.push({'id': 'call', 'name': BX.message('IM_CTL_CHAT_CALL'), 'title': '', 'more': BX.message('IM_CL_MORE_CALL'),  skip: !this.BXIM.webrtc.phoneEnabled});
			category.push({'id': 'ol', 'name': BX.message('IM_CTL_CHAT_OL'), 'title': '', 'more': BX.message('IM_CTL_CHAT_OL'), skip: this.BXIM.userExtranet});
			category.push({'id': 'extranet', 'name': BX.message('IM_CTL_CHAT_EXTRANET'), 'title': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'more': BX.message('IM_CL_MORE_EXTRANET_NEW')});
			category.push({'id': 'structure', 'name': this.BXIM.bitrixIntranet? BX.message('IM_CTL_CHAT_STRUCTURE'): BX.message('IM_CL_GROUP'), 'title': '', 'more': this.BXIM.bitrixIntranet? BX.message('IM_CL_MORE_STRUCTURE'): BX.message('IM_CL_MORE_GROUP'), skip: !showStructureBlock});
			category.push({'id': 'blocked', 'name': BX.message('IM_CTL_CHAT_BLOCKED'), 'title': '', 'more': BX.message('IM_CL_MORE_EXTRANET_NEW')});
		}
		else
		{
			category.push({'id': 'open', 'name': BX.message('IM_CTL_CHAT_OPEN_NEW'), 'title': BX.message('IM_CL_CREATE_OPEN_NEW'), 'more': BX.message('IM_CL_MORE_OPEN_NEW'), skip: !this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet});
			category.push({'id': 'chat', 'name': BX.message('IM_CTL_CHAT_CHAT_NEW'), 'title': BX.message('IM_CL_CREATE_CHAT_NEW'), 'more': BX.message('IM_CL_MORE_CHAT_NEW')});
			category.push({'id': 'lines', 'name': BX.message('IM_CTL_CHAT_LINES'), 'title': '', 'more': BX.message('IM_CL_MORE_LINES')});
			category.push({'id': 'call', 'name': BX.message('IM_CTL_CHAT_CALL'), 'title': '', 'more': BX.message('IM_CL_MORE_CALL'),  skip: !this.BXIM.webrtc.phoneEnabled});
			category.push({'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE_NEW_MSGVER_1'), 'title': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'more': BX.message('IM_CL_MORE_PRIVATE_NEW_MSGVER_1')});
			category.push({'id': 'bot', 'name': BX.message('IM_CTL_CHAT_BOT'), 'title': '', 'more': BX.message('IM_CL_MORE_BOT')});
			category.push({'id': 'ol', 'name': BX.message('IM_CTL_CHAT_OL'), 'title': '', 'more': BX.message('IM_CTL_CHAT_OL'), skip: this.BXIM.userExtranet});
			category.push({'id': 'extranet', 'name': BX.message('IM_CTL_CHAT_EXTRANET'), 'title': BX.message('IM_CL_CREATE_PRIVATE_NEW_MSGVER_1'), 'more': BX.message('IM_CL_MORE_EXTRANET_NEW')});
			category.push({'id': 'structure', 'name': this.BXIM.bitrixIntranet? BX.message('IM_CTL_CHAT_STRUCTURE'): BX.message('IM_CTL_CHAT_GROUP'), 'title': '', 'more': this.BXIM.bitrixIntranet? BX.message('IM_CL_MORE_STRUCTURE'): BX.message('IM_CL_MORE_GROUP'), skip: !showStructureBlock});
			category.push({'id': 'blocked', 'name': BX.message('IM_CTL_CHAT_BLOCKED'), 'title': '', 'more': BX.message('IM_CL_MORE_EXTRANET_NEW')});
		}

		for (var i = 0; i < category.length; i++)
		{
			if (category[i].skip)
				continue;

			categoryCount++;
		}

		var availContactListSize = contactListSize-(categorySize*categoryCount);
		var maxElementElements = parseInt(availContactListSize/elementSize);
		var maxElementPerCategory = Math.max(parseInt(availContactListSize/categoryCount/elementSize), minElementPerCategory);

		var showedElements = 0;
		var extraElements = 0;

		for (var i = 0; i < category.length; i++)
		{
			category[i].countElement = 0;

			if (category[i].skip)
				continue;

			category[i].countElement = maxElementPerCategory;
		}

		var arSearch = [];
		if (activeSearch)
		{
			searchText = searchText+'';
			if (!this.isMobile() && this.BXIM.language=='ru' && BX.correctText)
			{
				var correctText = BX.correctText(searchText);
				if (correctText != searchText)
				{
					searchText = searchText+" "+correctText;
				}
			}
			arSearch = searchText.split(" ");
		}

		var sortIndex = this.recentListGetSortIndex();
		var groupElements = {};
		var extraElementsGroup = [];
		for (var i = 0; i < category.length; i++)
		{
			groupElements[i] = [];
			if (category[i].id == 'private' || category[i].id == 'extranet' || category[i].id == 'blocked' || category[i].id == 'bot' || category[i].id == 'ol')
			{
				if (!viewBot && category[i].id == 'bot')
					category[i].skip = true;

				if (viewOnlyIntranet && category[i].id == 'extranet')
					category[i].skip = true;

				if (!viewChat && category[i].id == 'ol')
					category[i].skip = true;

				if (category[i].skip)
					continue;

				for (var userId in userListObject)
				{
					if (!userListObject.hasOwnProperty(userId))
						continue;

					if (!viewSelf && userId == this.BXIM.userId)
						continue;

					if (
						this.BXIM.messenger.users[userId].external_auth_id === 'imconnector'
						|| this.BXIM.messenger.users[userId].external_auth_id === 'call'
					)
					{
						continue;
					}

					if (typeof(this.BXIM.messenger.users[userId].active) != 'undefined' && !this.BXIM.messenger.users[userId].active)
						continue;

					if (!viewOffline)
					{
						var userOnlineStatus = this.getUserStatus(this.BXIM.messenger.users[userId]);
						if (viewOfflineWithPhones && this.userHasPhone(userId))
						{
						}
						else if (userOnlineStatus == "offline")
						{
							continue;
						}
					}

					var chatId = this.BXIM.messenger.userChat[userId];
					if (category[i].id == 'blocked')
					{
						if (
							!this.BXIM.messenger.userChatBlockStatus[chatId]
							|| !this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]
						)
						{
							continue;
						}
					}
					else
					{
						if (
							this.BXIM.messenger.userChatBlockStatus[chatId]
							&& this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]
						)
						{
							continue;
						}
					}

					if (category[i].id == 'extranet')
					{
						if (!this.BXIM.messenger.users[userId].extranet)
							continue;
					}
					else
					{
						if (this.BXIM.messenger.users[userId].extranet)
							continue;
					}

					if (category[i].id == 'ol')
					{
						if (!this.BXIM.messenger.users[userId].bot)
						{
							continue;
						}

						if (this.BXIM.bitrix24 && this.BXIM.messenger.bot[userId] && this.BXIM.messenger.bot[userId].code == 'network_cloud')
						{
							continue;
						}

						if (
							!this.BXIM.messenger.bot[userId]
							|| this.BXIM.messenger.bot[userId].type != 'network' && this.BXIM.messenger.bot[userId].type != 'support24'
						)
						{
							continue;
						}
					}
					else if (category[i].id == 'bot')
					{
						if (!this.BXIM.messenger.users[userId].bot || !this.BXIM.messenger.bot[userId])
							continue;

						if (this.BXIM.messenger.bot[userId] && this.BXIM.messenger.bot[userId].type == 'network')
							continue;

						if (this.BXIM.messenger.openChatFlag)
						{
							var currentChat = this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)];
							if (currentChat && currentChat.entity_type != "LINES" && this.BXIM.messenger.bot[userId].type == 'openline')
							{
								continue;
							}
							else if (currentChat && currentChat.entity_type == "LINES" && !this.BXIM.messenger.bot[userId].openline)
							{
								continue;
							}
							else if (this.BXIM.messenger.bot[userId].type == 'network' || this.BXIM.messenger.bot[userId].type == 'support24')
							{
								continue;
							}
						}
						else
						{
							if (
								this.BXIM.messenger.bot[userId].type == 'network'
								|| this.BXIM.messenger.bot[userId].type == 'support24'
								|| this.BXIM.messenger.bot[userId].type == 'openline'
							)
							{
								continue;
							}
						}
					}
					else
					{
						if (this.BXIM.messenger.users[userId].bot)
							continue;
					}

					if (activeSearch && searchStructureId)
					{
					}
					else if (activeSearch)
					{
						var user = this.BXIM.messenger.users[userId];
						if (!user)
						{
							continue;
						}
						var userSearchByName = user.name.toString().toLowerCase() + (user.search_mark? " " + user.search_mark: "");
						var userSearchByPosition = user.work_position? (" " + user.work_position).toLowerCase(): "";
						var skipUser = true;

						if (!sortIndex[userId])
						{
							sortIndex[userId] = 0;
						}
						for (var s = 0; s < arSearch.length; s++)
						{
							if (userSearchByName.indexOf(arSearch[s].toString().toLowerCase()) >= 0)
							{
								sortIndex[userId] += 100+arSearch[s].length;
								skipUser = false;
							}
							if (userSearchByPosition.indexOf(arSearch[s].toString().toLowerCase()) >= 0)
							{
								sortIndex[userId] += 50+arSearch[s].length;
								skipUser = false;
							}
						}
						if (skipUser)
						{
							continue;
						}
					}
					if (category[i].id == 'bot')
					{
						groupElements[i].push(this.BXIM.messenger.users[userId]);
					}
					else if (category[i].id == 'ol')
					{
						groupElements[i].push(this.BXIM.messenger.users[userId]);
					}
					else
					{
						groupElements[i].push(this.BXIM.messenger.users[userId]);
					}
				}
				if (category[i].id == 'bot')
				{
					groupElements[i].sort(BX.delegate(function(u1, u2) {
						var i1 = sortIndex[u1.id]? sortIndex[u1.id]: 0;
						var i2 = sortIndex[u2.id]? sortIndex[u2.id]: 0;

						if (this.BXIM.messenger.bot[u1.id] && this.BXIM.messenger.bot[u1.id]['code'] == "marta")
						{
							i1 = 10000000;
						}
						if (this.BXIM.messenger.bot[u2.id] && this.BXIM.messenger.bot[u2.id]['code'] == "marta")
						{
							i2 = 10000000;
						}

						if (i1 > i2) { return -1; }
						else if (i1 < i2) { return 1;}
						else{ return 0;}
					}, this));
				}
				else if (activeSearch)
				{
					groupElements[i].sort(function(u1, u2) {
						var i1 = sortIndex[u1.id]? sortIndex[u1.id]: 0;
						var i2 = sortIndex[u2.id]? sortIndex[u2.id]: 0;

						if (i1 > i2) { return -1; }
						else if (i1 < i2) { return 1;}
						else{ return 0;}
					});
				}
				else
				{
					groupElements[i].sort(function(u1, u2) {
						var i1 = sortIndex[u1.id]? sortIndex[u1.id]: 0;
						var i2 = sortIndex[u2.id]? sortIndex[u2.id]: 0;

						if (BXIM && u1.id == BXIM.userId)
						{
							i1 = 10000000;
						}
						if (BXIM && u2.id == BXIM.userId)
						{
							i2 = 10000000;
						}

						if (i1 > i2) { return -1; }
						else if (i1 < i2) { return 1;}
						else{ return 0;}
					});
				}
			}
			else if (category[i].id == 'chat' || category[i].id == 'open' || category[i].id == 'call' || category[i].id == 'lines')
			{
				if (!viewChat && category[i].id != 'open')
					category[i].skip = true;

				if (!viewOpenChat && category[i].id == 'open')
					category[i].skip = true;

				if (category[i].skip)
					continue;

				for (var chatId in this.BXIM.messenger.chat)
				{
					if (!this.BXIM.messenger.chat.hasOwnProperty(chatId))
					{
						continue;
					}

					if (
						this.BXIM.messenger.chat[chatId].type == 'chat'
						|| this.BXIM.messenger.chat[chatId].type == 'open'
						|| this.BXIM.messenger.chat[chatId].type == 'call'
						|| this.BXIM.messenger.chat[chatId].type == 'lines'
					)
					{
						if (this.BXIM.messenger.chat[chatId].type != category[i].id)
						{
							continue;
						}
					}
					else if (category[i].id != 'chat')
					{
						continue;
					}

					if (this.BXIM.messenger.generalChatId == chatId && (!this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet))
					{
						continue;
					}

					if (activeSearch)
					{
						var skipChat = true;
						for (var s = 0; s < arSearch.length; s++)
						{
							if (this.BXIM.messenger.chat[chatId].name.toString().toLowerCase().indexOf(arSearch[s].toString().toLowerCase()) >= 0)
							{
								skipChat = false;
								break;
							}
						}
						if (skipChat)
						{
							continue;
						}
					}

					groupElements[i].push(this.BXIM.messenger.chat[chatId]);
				}
				groupElements[i].sort(BX.delegate(function(u1, u2) {
					var i1 = sortIndex['chat'+u1.id]? sortIndex['chat'+u1.id]: 0;
					var i2 = sortIndex['chat'+u2.id]? sortIndex['chat'+u2.id]: 0;

					if (this.BXIM.messenger.generalChatId == u1.id)
					{
						i1 = 10000000;
					}
					else if (this.BXIM.messenger.userChatBlockStatus[u1.id] && this.BXIM.messenger.userChatBlockStatus[u1.id][this.BXIM.userId])
					{
						i1 = -1;
					}

					if (this.BXIM.messenger.generalChatId == u2.id)
					{
						i2 = 10000000;
					}
					else if (this.BXIM.messenger.userChatBlockStatus[i2.id] && this.BXIM.messenger.userChatBlockStatus[i2.id][this.BXIM.userId])
					{
						i2 = -1;
					}

					if (i1 > i2) { return -1; }
					else if (i1 > i2) { return -1; }
					else if (i1 < i2) { return 1;}
					else{ return 0;}
				}, this));
			}
			else if (category[i].id == 'olQueue')
			{
				if (!this.BXIM.messenger.openlines)
					continue;

				if (!this.BXIM.messenger.openlines.queue)
					continue;

				this.BXIM.messenger.openlines.queue.sort(function(i1, i2) {
					if (i1.transfer_count > i2.transfer_count) {
						return -1;
					}
					else if (i1.transfer_count < i2.transfer_count)
					{
						return 1;
					}
					else
					{
						if (i1.id > i2.id)
						{
							return 1;
						}
						else if (i1.id < i2.id)
						{
							return -1;
						}
						else
						{
							return 0;
						}
					}
				});
				for (var queueId = 0; queueId < this.BXIM.messenger.openlines.queue.length; queueId++)
				{
					if (activeSearch)
					{
						var skipItem = true;
						for (var s = 0; s < arSearch.length; s++)
						{
							if (this.BXIM.messenger.openlines.queue[queueId].name.toString().toLowerCase().indexOf(arSearch[s].toString().toLowerCase()) >= 0)
							{
								skipItem = false;
								break;
							}
						}
						if (skipItem)
						{
							continue;
						}
					}

					groupElements[i].push(BX.clone(this.BXIM.messenger.openlines.queue[queueId]));
				}
			}
			else if (category[i].id == 'structure')
			{
				if (category[i].skip)
				{
					continue;
				}
				for (var groupId in this.BXIM.messenger.groups)
				{
					if (!this.BXIM.messenger.userInGroup[groupId] || this.BXIM.messenger.userInGroup[groupId].length <= 0)
						continue;

					if (listName == 'popupChatDialogContactListElements' && this.BXIM.messenger.userInGroup[groupId].length > 200)
						continue;

					if (!showStructureSonetBlock && groupId.toString().substr(0,2) == 'SG')
						continue;

					if (activeSearch)
					{
						var skipItem = true;
						for (var s = 0; s < arSearch.length; s++)
						{
							if (this.BXIM.messenger.groups[groupId].name.toString().toLowerCase().indexOf(arSearch[s].toString().toLowerCase()) >= 0)
							{
								skipItem = false;
								break;
							}
						}
						if (skipItem)
						{
							continue;
						}
					}

					groupElements[i].push(this.BXIM.messenger.groups[groupId]);
				}

				groupElements[i].sort(BX.delegate(function(u1, u2) {
					var i1 = u1.id;
					var i2 = u2.id;

					if (this.BXIM.messenger.userInGroup[i1] && this.BXIM.messenger.userInGroup[i1].users.indexOf(this.BXIM.userId.toString()) > -1)
					{
						i1 = -1;
					}
					if (this.BXIM.messenger.userInGroup[i2] && this.BXIM.messenger.userInGroup[i2].users.indexOf(this.BXIM.userId.toString()) > -1)
					{
						i2 = -1;
					}

					if (i1 > i2) { return 1; }
					else if (i1 < i2) { return -1;}
					else{ return 0;}
				}, this));
			}

			if (category[i].countElement > groupElements[i].length)
			{
				showedElements += groupElements[i].length;
				extraElements += category[i].countElement-groupElements[i].length;
			}
			else
			{
				extraElementsGroup.push(i);
				showedElements += category[i].countElement;
			}
		}

		if (showedElements < maxElementElements)
		{
			var categoryId = 0;
			var maxCategoryId = extraElementsGroup.length;

			for (var i = 0; i < extraElements; i++)
			{
				if (extraElementsGroup[categoryId] && category[extraElementsGroup[categoryId]])
				{
					category[extraElementsGroup[categoryId]].countElement = category[extraElementsGroup[categoryId]].countElement+1;
				}
				categoryId = categoryId == maxCategoryId-1? 0: categoryId+1;
			}
		}

		for (var i = 0; i < category.length; i++)
		{
			if (category[i].skip)
				continue;

			if (activeSearch && groupElements[i].length <= 0)
			{
				if (!showBitrix24Search || category[i].id != 'extranet')
				{
					continue;
				}
			}

			if (groupElements[i].length <= 0 && !(category[i].id == 'private' || category[i].id == 'open' || category[i].id == 'chat' || showBitrix24Search && category[i].id == 'extranet'))
				continue;

			chatList.appendChild(BX.create("div", {props : { className: "bx-messenger-chatlist-group"}, children : [
				(!extraEnable || category[i].id == 'lines' || category[i].id == 'call' || category[i].id == 'blocked' || category[i].id == 'bot' || category[i].id == 'ol')? null: BX.create("span", {attrs: {'data-type': category[i].id}, props : { title: category[i].title, className: "bx-messenger-chatlist-group-add"}}),
				BX.create("span", {props : { className: "bx-messenger-chatlist-group-title"}, html : category[i].name})
			]}));

			if (groupElements[i].length <= 0)
			{
				if (showBitrix24Search && category[i].id == 'extranet')
				{
					chatList.appendChild(BX.create("div", {props : { className: "bx-messenger-chatlist-search-button-wrap"}, children : [
						BX.create("span", {props : { className: "bx-messenger-chatlist-search-button"}, html: this.BXIM.bitrixIntranet? BX.message('IM_SEARCH_B24_MSGVER_1'): BX.message('IM_SEARCH_SITE')})
					]}));
				}

				continue;
			}

			var categoryItems = [];
			var countElements = 1;
			for (var j = 0; j < groupElements[i].length; j++)
			{
				var isShown = countElements <= category[i].countElement;

				countElements++;

				if (category[i].id == 'private' || category[i].id == 'extranet' || category[i].id == 'bot' || category[i].id == 'ol')
				{
					var user = groupElements[i][j];

					var node = this.drawContactListElement({
						'id': user.id,
						'data': user,
						'showUserLastActivityDate': category[i].id == 'bot'? false: showUserLastActivityDate,
						'showLastMessage': false,
						'showCounter': extraEnable,
						'extraClass': isShown? '': 'bx-messenger-hide'
					});
					if (node)
					{
						categoryItems.push(node);
					}
				}
				else if (category[i].id == 'chat' || category[i].id == 'open' || category[i].id == 'call' || category[i].id == 'lines')
				{
					var chat = groupElements[i][j];
					var node = this.drawContactListElement({
						'id': 'chat'+chat.id,
						'data': chat,
						'showLastMessage': false,
						'showCounter': extraEnable,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					});
					if (node)
					{
						categoryItems.push(node);
					}
				}
				else if (category[i].id == 'olQueue' || category[i].id == 'viQueue')
				{
					var queue = groupElements[i][j];
					queue.type = category[i].id;
					var node = this.drawContactListElement({
						'id': 'queue'+queue.id,
						'data': queue,
						'showLastMessage': false,
						'showCounter': false,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					});
					if (node)
					{
						categoryItems.push(node);
					}
				}
				else if (category[i].id == 'structure')
				{
					var structure = groupElements[i][j];
					var node = this.drawContactListElement({
						'id': 'structure'+structure.id,
						'data': structure,
						'showLastMessage': false,
						'showCounter': false,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					});
					if (node)
					{
						categoryItems.push(node);
					}
				}
			}

			if (category[i].countElement < groupElements[i].length)
			{
				categoryItems.push(BX.create("div", {props : { className: "bx-messenger-chatlist-more-wrap"}, children : [
					BX.create("span", {attrs: {
						'data-id': category[i].id,
						'data-text': BX.message('IM_CL_MORE').replace("#COUNT#", groupElements[i].length-category[i].countElement),
						'data-title': category[i].more
					}, props : {
						title: category[i].more,
						className: "bx-messenger-chatlist-more"
					},
					html : this.BXIM.messenger.contactListShowed[category[i].id]? BX.message('IM_CL_HIDE'): BX.message('IM_CL_MORE').replace("#COUNT#", groupElements[i].length-category[i].countElement)})
				]}));
			}
			if (categoryItems.length > 0)
			{
				chatList.appendChild(BX.create("div", {props : { className: "bx-messenger-chatlist-category"+(this.BXIM.messenger.contactListShowed[category[i].id]? ' bx-messenger-chatlist-show-all': '')}, children : categoryItems}));

				if (showBitrix24Search && category[i].id == 'extranet')
				{
					chatList.appendChild(BX.create("div", {props : { className: "bx-messenger-chatlist-search-button-wrap"}, children : [
						BX.create("span", {props : { className: "bx-messenger-chatlist-search-button"}, html: BX.message('IM_SEARCH_B24_MSGVER_1')})
					]}));
				}
			}
		}

		if (searchWaitBackend)
		{
			chatList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-search"},
				html : BX.message('IM_M_CL_SEARCH')
			}));
		}
		else if (chatList.childNodes.length <= 0)
		{
			chatList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
			callback.empty();
		}

		return chatList;
	};

	MessengerCommon.prototype.userInviteResend = function(userId)
	{
		if (!this.BXIM.canInvite)
		{
			var recentElement = this.recentListGetItem(userId);
			if (recentElement && recentElement.invited && recentElement.invited.originator_id != this.BXIM.userId)
			{
				return false;
			}
		}

		BX.ajax.runAction('intranet.controller.invite.reinvite', {data: {
			params: {
				userId: userId
			}
		}}).then(function(response) {
			BX.UI.Notification.Center.notify({
				content: BX.message('IM_USER_INVITE_RESEND_DONE'),
				autoHideDelay: 2000
			});
		}, function(response) {
			if (response.status == 'error' && response.errors.length > 0)
			{
				var errorContent = response.errors.map(function(element) {
					return element.message;
				}).join('. ');

				BX.UI.Notification.Center.notify({
					content : errorContent,
					autoHideDelay : 4000
				});

				return true;
			}

			BX.UI.Notification.Center.notify({
				content: BX.message('IM_CONNECT_ERROR'),
				autoHideDelay: 4000
			});
		});
	}

	MessengerCommon.prototype.userInviteCancel = function(userId)
	{
		if (!this.BXIM.canInvite)
		{
			var recentElement = this.recentListGetItem(userId);
			if (recentElement && recentElement.invited && recentElement.invited.originator_id != this.BXIM.userId)
			{
				return false;
			}
		}

		var element = this.recentListGetItem(userId);
		var user = this.BXIM.messenger.users[userId];
		if (element)
		{
			this.recentListHide(userId, false);
		}
		if (user)
		{
			delete this.BXIM.messenger.users[userId];
			if (!this.BXIM.messenger.recentList)
			{
				this.userListRedraw();
			}
		}

		BX.ajax.runAction('intranet.controller.invite.deleteinvitation', {data: {
			params: {
				userId: userId
			}
		}}).then(function (response) {
			BX.UI.Notification.Center.notify({
				content: BX.message('IM_USER_INVITE_CANCEL_DONE'),
				autoHideDelay: 2000
			});
		}.bind(this), function (response) {
			if (user)
			{
				this.BXIM.messenger.users[user.id] = user;
			}
			if (element)
			{
				this.recentListAddItem(element);
			}
			this.userListRedraw();

			if (response.status == 'error' && response.errors.length > 0)
			{
				var errorContent = response.errors.map(function(element) {
					return element.message;
				}).join('. ');

				BX.UI.Notification.Center.notify({
					content : errorContent,
					autoHideDelay : 4000
				});

				return true;
			}

			BX.UI.Notification.Center.notify({
				content: BX.message('IM_CONNECT_ERROR'),
				autoHideDelay: 4000
			});
		}.bind(this));

		return true;
	}

	MessengerCommon.prototype.userHasPhone = function(userId)
	{
		return (
			this.BXIM.messenger.users.hasOwnProperty(userId) && this.BXIM.messenger.users[userId].phone_device
			|| (this.BXIM.messenger.phones.hasOwnProperty(userId) && (
				this.BXIM.messenger.phones[userId].hasOwnProperty('PERSONAL_MOBILE')
				|| this.BXIM.messenger.phones[userId].hasOwnProperty('PERSONAL_PHONE')
				|| this.BXIM.messenger.phones[userId].hasOwnProperty('WORK_PHONE')
			)
		));
	};

	MessengerCommon.prototype.userChangeStatus = function(params)
	{
		var users;
		if (BX.type.isArray(params))
		{
			users = params;
		}
		else
		{
			users = [params];
		}

		var contactListRedraw = false;
		var dialogStatusRedraw = false;
		var changeCurrentUser = false;

		users.forEach(function(user)
		{
			if (typeof(this.BXIM.messenger.users[user.id]) == 'undefined')
			{
				return;
			}

			if (user.id === this.getCurrentUser())
			{
				changeCurrentUser = true;
			}

			var storedUser = this.BXIM.messenger.users[user.id];

			if (
				!contactListRedraw
				&& this.BXIM.messenger.recent.findIndex(function(element) { return element.id == user.id }) > -1
			)
			{
				if (storedUser.status !== user.status)
				{
					contactListRedraw = true;
				}

				if (
					!contactListRedraw
					&& typeof user.idle !== 'undefined'
				)
				{
					var storedIdle = storedUser.idle ? storedUser.idle.getTime() : 0;
					var newIdle = user.idle ? new Date(user.idle).getTime() : 0;

					if (storedIdle !== newIdle)
					{
						contactListRedraw = true;
					}
				}

				if (
					!contactListRedraw
					&& typeof user.mobile_last_date !== 'undefined'
				)
				{
					var storedMobileLastDate = storedUser.mobile_last_date ? storedUser.mobile_last_date.getTime() : 0;
					var newMobileLastDate = user.mobile_last_date ? new Date(user.mobile_last_date).getTime() : 0;

					if (storedMobileLastDate !== newMobileLastDate)
					{
						contactListRedraw = true;
					}
				}
			}

			if (
				contactListRedraw
				&& this.BXIM.messenger.currentTab.toString() == user.id.toString()
			)
			{
				dialogStatusRedraw = true;
			}

			if (typeof user.status !== 'undefined')
			{
				storedUser.status = user.status;
			}
			if (typeof user.color !== 'undefined')
			{
				storedUser.color = user.color;
			}
			if (typeof user.idle !== 'undefined')
			{
				storedUser.idle = user.idle? new Date(user.idle): false;
			}
			if (typeof user.mobile_last_date !== 'undefined')
			{
				storedUser.mobile_last_date = user.mobile_last_date? new Date(user.mobile_last_date): false;
			}
			if (typeof user.last_activity_date !== 'undefined')
			{
				storedUser.last_activity_date = user.last_activity_date? new Date(user.last_activity_date): false;
			}
		}.bind(this));

		if (changeCurrentUser)
		{
			this.BXIM.messenger.setStatus(this.BXIM.messenger.users[this.getCurrentUser()].status, false);
		}

		if (dialogStatusRedraw)
		{
			this.BXIM.messenger.dialogStatusRedraw();
		}

		return true;
	};


	/* Section: Message */

	MessengerCommon.prototype.prepareCommandList = function(search)
	{
		search = typeof (search) == 'string'? search: '';

		var commandListOriginal = BX.clone(this.BXIM.messenger.command);

		var commandList = [];
		var commandListOther = [];
		for (var i = 0; i < commandListOriginal.length; i++)
		{
			if (this.BXIM.messenger.openChatFlag)
			{
				if (BX.MessengerCommon.userInChat(this.BXIM.messenger.currentTab.toString().substr(4), commandListOriginal[i].bot_id))
				{
					commandList.push(commandListOriginal[i]);
				}
				else
				{
					commandListOther.push(commandListOriginal[i]);
				}
			}
			else
			{
				if (this.BXIM.messenger.currentTab == parseInt(commandListOriginal[i].bot_id))
				{
					commandList.push(commandListOriginal[i]);
				}
				else
				{
					commandListOther.push(commandListOriginal[i]);
				}
			}
		}
		for (var i = 0; i < commandListOther.length; i++)
		{
			commandList.push(commandListOther[i]);
		}

		var list = [];
		var categoryName = '';
		for (var i = 0; i < commandList.length; i++)
		{
			if (search == '' || commandList[i].command.indexOf(search) === 1)
			{
				if (commandList[i].command == '/>>')
				{
					commandList[i].command = '>>';
				}
				if (
					this.BXIM.messenger.openLinesFlag
					&& (
						commandList[i].command == '/me'
						|| commandList[i].command == '/loud'
					)
				)
				{
					continue;
				}

				if (this.BXIM.userExtranet && !commandList[i].extranet)
				{
					continue;
				}

				if (!commandList[i].common)
				{
					if (this.BXIM.messenger.openChatFlag)
					{
						if (!BX.MessengerCommon.userInChat(this.BXIM.messenger.currentTab.toString().substr(4), commandList[i].bot_id))
						{
							continue;
						}
					}
					else if (this.BXIM.messenger.currentTab != parseInt(commandList[i].bot_id))
					{
						continue;
					}
				}

				if (commandList[i].context != '')
				{
					if (commandList[i].context == 'chat')
					{
						if (!this.BXIM.messenger.openChatFlag)
						{
							continue;
						}
					}
					else if (commandList[i].context == 'user')
					{
						if (this.BXIM.messenger.openChatFlag)
						{
							continue;
						}
					}
					else if (search == '')
					{
						continue;
					}
				}

				if (categoryName != commandList[i].category)
				{
					categoryName = commandList[i].category;
					list.push({
						'type': 'category',
						'title': categoryName
					});
				}

				commandList[i].type = 'item';
				list.push(commandList[i]);
			}
		}
		return list;
	}

	MessengerCommon.prototype.convertMessage = function(message)
	{
		if (typeof message.textOriginal !== 'undefined')
		{
			return message;
		}

		message.textOriginal = message.text;
		message.text = BX.MessengerCommon.prepareText(message.text, true, true, true);

		return message;
	}

	MessengerCommon.prototype.drawMessage = function(dialogId, message, scroll, appendTop)
	{
		if (typeof(message) != 'object' || this.BXIM.messenger.popupMessenger == null)
			return false;

		message = this.convertMessage(message);

		var placeholder = this.BXIM.messenger.popupMessengerBodyWrap;
		var placeholderName = 'default';
		var customPlace = false;
		var showKeyboard = true;
		var showReply = true;

		if (typeof(dialogId) == "object")
		{
			customPlace = true;

			placeholderName = dialogId.placeholderName || 'custom';
			placeholder = dialogId.placeholder;
			showKeyboard = dialogId.showKeyboard == false? false: true;
			showReply = dialogId.showReply == false? false: true;
		}
		else if (dialogId != this.BXIM.messenger.currentTab || dialogId == 0 || !this.MobileActionEqual('DIALOG'))
		{
			return false;
		}

		if (message.dropDuplicate)
		{
			var duplicateMessage = BX.findChildByClassName(placeholder, "bx-messenger-content-item-id-"+message.id);
			if (duplicateMessage)
			{
				BX.remove(duplicateMessage);
			}
			message.dropDuplicate = false;
		}

		appendTop = appendTop == true;
		scroll = appendTop? false: scroll;

		if (typeof(message.params) != 'object')
		{
			message.params = {};
		}

		var isChat = false;
		var isGeneralChat = false;
		var edited = message.params && message.params.IS_EDITED == 'Y';
		var deleted = message.params && message.params.IS_DELETED == 'Y';

		var messageText = deleted? BX.message('IM_M_DELETED'): message.text;
		var temp = message.id.toString().indexOf('temp') == 0;
		var retry = temp && message.retry;
		var system = message.senderId == 0;
		var likeEnable = this.BXIM.ppServerStatus;
		var withAppsMenu = message.params && message.params.MENU && message.params.MENU != 'N';

		messageText = this.replaceDateText(message.id, messageText, message.params);

		if (customPlace)
		{
			isChat = message.chatId && this.BXIM.messenger.chat[message.chatId]? true: false;
			isGeneralChat = isChat && message.chatId == this.BXIM.messenger.generalChatId;

			if (isChat && this.BXIM.messenger.chat[message.chatId].type == "call")
			{
				likeEnable = false;
			}
			else if (isChat && this.BXIM.messenger.chat[message.chatId].type == "lines")
			{
				var sourceId = this.linesGetSource(this.BXIM.messenger.chat[message.chatId]);
				if (!(sourceId == 'livechat'))
				{
					likeEnable = false;
				}
			}
			else if (!isChat && this.BXIM.messenger.bot[message.recipientId] && (this.BXIM.messenger.bot[message.recipientId].type == 'network' || this.BXIM.messenger.bot[message.recipientId].type == 'support24'))
			{
				likeEnable = false;
			}
		}
		else
		{
			if (message.senderId == this.BXIM.userId)
			{
				if (this.BXIM.messenger.message[message.id] && this.BXIM.messenger.message[message.id].recipientId == this.BXIM.messenger.currentTab)
				{
					this.BXIM.messenger.popupMessengerLastMessage = message.id;
				}
			}
			this.BXIM.messenger.openChatFlag = this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat';
			isChat = this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && (this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "chat" || this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "open");
			isGeneralChat = isChat && message.chatId == this.BXIM.messenger.generalChatId;

			if (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "call")
			{
				likeEnable = false;
			}
			else if (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "lines")
			{
				var sourceId = this.linesGetSource(this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)]);
				if (!(sourceId == 'livechat'))
				{
					likeEnable = false;
				}
			}
			else if (!this.BXIM.messenger.openChatFlag && this.BXIM.messenger.bot[this.BXIM.messenger.currentTab] && (this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type == 'network' || this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type == 'support24'))
			{
				likeEnable = false;
			}
		}

		var likeCount = likeEnable && typeof(message.params.LIKE) == "object" && message.params.LIKE.length > 0? message.params.LIKE.length: '';
		var iLikeThis = likeEnable && typeof(message.params.LIKE) == "object" && BX.util.in_array(this.BXIM.userId, message.params.LIKE);

		var filesNode = this.diskDrawFiles(message.chatId, message.params.FILE_ID);
		if (filesNode.length > 0)
		{
			var filesNodeWithText = messageText != '' || message.params.ATTACH;
			filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(filesNodeWithText? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
		}
		else
		{
			filesNode = null;
		}

		var messageReplyNode = showReply? this.drawMessageReply(message.id): null;

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


		if (
			message.params.LINK_ACTIVE
			&& message.params.LINK_ACTIVE.length > 0
			&& !message.params.LINK_ACTIVE.map(function(userId) { return parseInt(userId) }).includes(this.BXIM.userId)
		)
		{
			messageText = messageText.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/gi, '$2');
		}

		var extraClass = "";
		if (message.params.CLASS)
		{
			extraClass = message.params.CLASS;
		}

		var extraNode = null;
		if (message.params.IMOL_SID && parseInt(message.params.IMOL_SID) > 0)
		{
			extraNode = BX.create("div", {
				props : { className : "bx-messenger-message-extra"},
				html: BX.message('IM_OL_DIALOG_NUMBER').replace("#NUMBER#", message.params.IMOL_SID)}
			);
		}

		if (message.params.IMOL_FORM && this.BXIM.messenger.chat[message.chatId] && this.BXIM.messenger.chat[message.chatId].type == 'livechat')
		{
			var delay = message.params.IMOL_FORM.toString().substr(-6) == '-delay';
			var formType = delay? message.params.IMOL_FORM.substr(0, message.params.IMOL_FORM.lastIndexOf('-delay')): message.params.IMOL_FORM;

			if (this.BXIM.messenger.popupMessengerLiveChatDelayedFormMid < message.id && this.BXIM.messenger.popupMessengerLiveChatFormType != formType)
			{
				this.BXIM.messenger.popupMessengerLiveChatDelayedFormMid = message.id;
				this.BXIM.messenger.popupMessengerLiveChatDelayedForm = delay? formType: null;
				this.BXIM.messenger.linesLivechatFormHide();

				clearTimeout(this.BXIM.messenger.popupMessengerLiveChatActionTimeout);
				this.BXIM.messenger.popupMessengerLiveChatActionTimeout = setTimeout(BX.delegate(function() {
					this.BXIM.messenger.linesLivechatFormShow(formType);
				}, this), delay? 30000: 5000);
			}
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

		var keyboardNode = this.drawKeyboard(message.recipientId, message.id, (showKeyboard && message.params.KEYBOARD? message.params.KEYBOARD: null));

		var skipAddMessage = false;
		if (!filesNode && !attachNode && messageText.length <= 0)
		{
			skipAddMessage = true;
		}

		if (message.system && message.system == 'Y')
		{
			system = true;
			message.senderId = 0;
		}

		var addBlankNode = false;
		var messageUser = message.senderId > 0 ? this.BXIM.messenger.users[message.senderId] : undefined;

		if (!system && (typeof(messageUser) == 'undefined' || messageUser.id <= 0))
		{
			addBlankNode = true;
			skipAddMessage = true;
		}

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
		var voteBlock = this.linesVoteDraw(message.id);
		if (voteBlock)
		{
			messageText = voteBlock;
			message.system = 'Y';
		}
		else
		{
			extraClass = extraClass.replace('bx-messenger-content-item-vote', '');

			var voteResultBlock = this.linesVoteResultDraw(message.id, messageText);
			if (voteResultBlock)
			{
				messageText = voteResultBlock;
			}
		}


		if (!customPlace)
		{
			if (!this.BXIM.messenger.history[dialogId])
				this.BXIM.messenger.history[dialogId] = [];

			if (parseInt(message.id) > 0 && this.BXIM.messenger.history[dialogId].indexOf(message.id.toString()) == -1)
				this.BXIM.messenger.history[dialogId].push(message.id);

			var messageId = 0;
			if (!addBlankNode)
			{
				var markNewMessage = false;
				if (this.BXIM.messenger.unreadMessage[dialogId] && BX.util.in_array(message.id, this.BXIM.messenger.unreadMessage[dialogId]))
					markNewMessage = true;
			}
		}

		var insertBefore = false;
		var lastMessage = null;

		if (appendTop)
		{
			lastMessage = placeholder.firstChild;
			if (lastMessage)
			{
				if (BX.hasClass(lastMessage, "bx-messenger-content-empty") || BX.hasClass(lastMessage, "bx-messenger-content-load"))
				{
					BX.remove(lastMessage);
				}
				else if (BX.hasClass(lastMessage, "bx-messenger-content-group"))
				{
					lastMessage = lastMessage.nextSibling;
				}
			}
		}
		else
		{
			lastMessage = placeholder.lastChild;

			if (lastMessage && (BX.hasClass(lastMessage, "bx-messenger-content-empty") || BX.hasClass(lastMessage, "bx-messenger-content-load")))
			{
				BX.remove(lastMessage);
			}
			else if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify"))
			{
				if (message.senderId == this.BXIM.messenger.currentTab || !this.countWriting(this.BXIM.messenger.currentTab))
				{
					BX.remove(lastMessage);
					insertBefore = false;
					lastMessage = placeholder.lastChild;
				}
				else
				{
					insertBefore = true;
					lastMessage = placeholder.lastChild.previousSibling;
				}
			}
		}

		if (!addBlankNode)
		{
			var dateGroupTitle = this.formatDate(message.date, this.getDateFormatType('MESSAGE_TITLE'));
			var dataGroupCode = (typeof(BX.translit) != 'undefined'? BX.translit(dateGroupTitle): dateGroupTitle);

			if (typeof(this.messageGroup) != 'object')
			{
				this.messageGroup = {};
			}
			if (typeof(this.messageGroup[placeholderName]) != 'object')
			{
				this.messageGroup[placeholderName] = {};
			}

			if (!this.messageGroup[placeholderName][dataGroupCode])
			{
				this.messageGroup[placeholderName][dataGroupCode] = true;
				var dateGroupChildren = [];
				if (this.BXIM.desktop && this.isPage())
				{
					dateGroupChildren = [
						BX.create("a", {
							attrs : {name : 'bx-im-go-' + message.date},
							props : {className : "bx-messenger-content-group-link"}
						}),
						BX.create("a", {
							attrs : {id : 'bx-im-go-' + dataGroupCode, href : "#bx-im-go-" + message.date},
							props : {className : "bx-messenger-content-group-title" + (this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')},
							html : dateGroupTitle
						})
					];
				}
				else
				{
					dateGroupChildren = [
						BX.create("a", {
							attrs : {name : 'bx-im-go-' + message.date},
							props : {className : "bx-messenger-content-group-link"}
						}),
						BX.create("div", {
							attrs : {id : 'bx-im-go-' + dataGroupCode},
							props : {className : "bx-messenger-content-group-title" + (this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')},
							html : dateGroupTitle
						})
					]
				}

				var dateGroupNode = BX.create("div", {
					props : {className : "bx-messenger-content-group" + (dateGroupTitle == BX.message('FD_TODAY')? " bx-messenger-content-group-today": "")},
					children : dateGroupChildren
				});

				if (appendTop)
				{
					placeholder.insertBefore(dateGroupNode, placeholder.firstChild);
					lastMessage = dateGroupNode.nextSibling;
				}
				else
				{
					if (insertBefore && lastMessage.nextElementSibling)
					{
						placeholder.insertBefore(dateGroupNode, lastMessage.nextElementSibling);
						lastMessage = dateGroupNode;
					}
					else
					{
						placeholder.appendChild(dateGroupNode);
					}
				}
			}
		}

		var messageWithoutPadding = false;
		var messageOnlyRichLink = false;


		var textNode = null;
		if (typeof(messageText) == 'string')
		{
			if (messageText.length > 0)
			{
				var checkImages = messageText.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1').replace(/<\/?[^>]+>/gi, ' ').replace(/(https|http):\/\/([\S]+)\.(jpg|jpeg|png|gif|webp)(\?[\S]+)?/ig, function(whole) {return '';}).trim();
				if (!checkImages)
				{
					messageWithoutPadding = true;
				}
			}

			if (
				this.BXIM.settings.enableRichLink
				&& message.params.URL_ONLY == 'Y'
				&& message.params.URL_ID && message.params.URL_ID.length > 0
				&& message.params.ATTACH && message.params.ATTACH.length > 0
			)
			{
				messageOnlyRichLink = true;
			}

			parsedText = !temp? messageText: this.prepareText(
				messageText,
				false,
				true,
				true,
				(!this.BXIM.messenger.openChatFlag || message.senderId == this.BXIM.userId? false: (this.BXIM.messenger.users[this.BXIM.userId].name)),
				objectReference
			);

			var objectReference = {oneSmileInMessage: false}
			textNode = BX.create("span", {
				props : { className : "bx-messenger-message"},
				attrs: {'id' : 'im-message-'+message.id},
				html: parsedText
			});
			var oneSmileInMessage = objectReference.oneSmileInMessage;
		}
		else
		{
			textNode = BX.create("span", {
				props : { className : "bx-messenger-message"},
				attrs: {'id' : 'im-message-'+message.id},
				children: [messageText]}
			);
			var oneSmileInMessage = false;
		}

		var isContentWithLargeFont = message.params.LARGE_FONT == 'Y' && this.BXIM.settings.enableBigSmile;

		if (!skipAddMessage)
		{
			if (lastMessage)
				messageId = lastMessage.getAttribute('data-messageId');

			if (system)
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'system', 'data-senderId' : "0", 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-id-"+message.id+" bx-messenger-content-item-notice "+extraClass}, children: [
					extraNode,
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(oneSmileInMessage? " bx-messenger-content-item-content-transparent": "")+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")+(isContentWithLargeFont?" bx-messenger-content-item-content-large-font": "")}, children : [
						!isGeneralChat? []: BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
						!this.isMobile() || !likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, children: [
							BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: ''}),
							BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"}, html: likeCount})
						], events: this.isMobile()? {click: BX.delegate(function (e){
							this.BXIM.messageLike(message.id);
							return BX.PreventDefault(e);
						}, this)}: {}}),
						typeof(messageUser) == 'undefined' || messageUser.id <= 0? []:
						BX.create("span", { props : { className : "bx-messenger-content-item-avatar"}, children : [
							BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
							BX.create("span", { props : { className : "bx-messenger-content-item-avatar-block"}, children: [
								BX.create('span', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: 'background: url(\''+this.formatUrl(messageUser.avatar)+'\'); background-size: cover;')}}),
								this.BXIM.messenger.openChatFlag? BX.create("span", { props : { className : "bx-messenger-content-item-avatar-name"}, attrs : { title: BX.util.htmlspecialcharsback(messageUser.name)}, html: messageUser.first_name? messageUser.first_name: messageUser.name.split(" ")[0]}): null
							]})
						]}),
						BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children: []}),
						BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
							BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")+(deleted?" bx-messenger-message-deleted": " ")}, children: [
									filesNode, textNode, attachNode
								]})
							]}),
							BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
								BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: this.formatDate(message.date, this.getDateFormatType('MESSAGE'))}),
								message.params.BETA != 'Y'? null: BX.create("span", { props : { className : "bx-messenger-content-item-beta"}, attrs: {title: BX.message('IM_BETA')}, html: '<beta>&beta;</beta>'}),
							]}),
							BX.create("span", { props : { className : "bx-messenger-clear"}})
						]})
					]}),
					keyboardNode,
					messageReplyNode
				]});

				if (message.system && message.system == 'Y' && markNewMessage)
					BX.addClass(arMessage, 'bx-messenger-content-item-new');

			}
			else if (message.senderId == this.BXIM.userId)
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'self', 'data-senderId' : message.senderId, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-id-"+message.id+" bx-messenger-content-item-1 "+extraClass}, children: [
					extraNode,
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(oneSmileInMessage? " bx-messenger-content-item-content-transparent": "")+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")+(isContentWithLargeFont?" bx-messenger-content-item-content-large-font": "")}, children : [
						BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
						!this.isMobile() || !likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, children: [
							BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: ''}),
							BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"}, html: likeCount})
						], events: this.isMobile()? {click: BX.delegate(function (e){
							this.BXIM.messageLike(message.id);
							return BX.PreventDefault(e);
						}, this)}: {}}),
						BX.create("span", { props : { className : "bx-messenger-content-item-avatar"}, children : [
							BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
							BX.create("span", { props : { className : "bx-messenger-content-item-avatar-block"}, children: [
								BX.create('span', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: 'background: url(\''+this.formatUrl(messageUser.avatar)+'\'); background-size: cover;')}}),
								this.BXIM.messenger.openChatFlag? BX.create("span", { props : { className : "bx-messenger-content-item-avatar-name"}, attrs : { title: BX.util.htmlspecialcharsback(messageUser.name)}, html: messageUser.first_name? messageUser.first_name: messageUser.name.split(" ")[0]}): null
							]})
						]}),
						retry? (
							BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children:[
								BX.create("span", { attrs: { title: BX.message('IM_M_RETRY'), 'data-messageid': message.id, 'data-chat': parseInt(message.recipientId) > 0? 'Y':'N' }, props : { className : "bx-messenger-content-item-error"}, children:[
									BX.create("span", { props : { className : "bx-messenger-content-item-error-icon"}})
								]})
							]})
						):(
							BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children: temp?[
								BX.create("span", { props : { className : "bx-messenger-content-item-progress"}})
							]: []})
						),
						BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
							BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")+(deleted?" bx-messenger-message-deleted": " ")}, children: [
									filesNode, textNode, attachNode
								]})
							]}),
							BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
								!likeEnable || this.isMobile()? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, children: [
									BX.create("span", { html: '&nbsp;'}),
									BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"}, html: likeCount}),
									BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message('IM_MESSAGE_LIKE')})
								]}),
								BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: (retry? BX.message('IM_M_NOT_DELIVERED') : this.formatDate(message.date, this.getDateFormatType('MESSAGE')))}),
								message.params.BETA != 'Y'? null: BX.create("span", { props : { className : "bx-messenger-content-item-beta"}, attrs: {title: BX.message('IM_BETA')}, html: '<beta>&beta;</beta>'}),
							]}),
							BX.create("span", { props : { className : "bx-messenger-clear"}})
						]})
					]}),
					keyboardNode,
					messageReplyNode
				]});
			}
			else
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'other', 'data-senderId' : message.senderId, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-id-"+message.id+" bx-messenger-content-item-2"+(markNewMessage? ' bx-messenger-content-item-new': '')+" "+extraClass}, children: [
					extraNode,
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(oneSmileInMessage? " bx-messenger-content-item-content-transparent": "")+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")+(isContentWithLargeFont?" bx-messenger-content-item-content-large-font": "")}, children : [
						BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
						!this.isMobile() || !likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, children: [
							BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: ''}),
							BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"}, html: likeCount})
						], events: this.isMobile()? {click: BX.delegate(function (e){
							this.BXIM.messageLike(message.id);
							return BX.PreventDefault(e);
						}, this)}: {}}),
						BX.create("span", { attrs: {title: BX.util.htmlspecialcharsback(messageUser.name)}, props : { className : "bx-messenger-content-item-avatar bx-messenger-content-item-avatar-button"}, children : [
							BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
							BX.create("span", { props : { className : "bx-messenger-content-item-avatar-block"}, children: [
								BX.create('span', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: 'background: url(\''+this.formatUrl(messageUser.avatar)+'\'); background-size: cover;')}}),
								this.BXIM.messenger.openChatFlag || messageUser.bot? BX.create("span", { props : { className : "bx-messenger-content-item-avatar-name"}, attrs : { title: BX.util.htmlspecialcharsback(messageUser.name)}, html: messageUser.first_name? messageUser.first_name: messageUser.name.split(" ")[0]}): null
							]})
						]}),
						BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children:[]}),
						BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
							BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")+(deleted?" bx-messenger-message-deleted": " ")}, children: [
									filesNode, textNode, attachNode
								]})
							]}),
							BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
								!likeEnable || this.isMobile()? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, children: [
									BX.create("span", { html: '&nbsp;'}),
									BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"}, html: likeCount}),
									BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message('IM_MESSAGE_LIKE')})
								]}),
								BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: this.formatDate(message.date, this.getDateFormatType('MESSAGE'))}),
								message.params.BETA != 'Y'? null: BX.create("span", { props : { className : "bx-messenger-content-item-beta"}, attrs: {title: BX.message('IM_BETA')}, html: '<beta>&beta;</beta>'}),
							]}),
							BX.create("span", { props : { className : "bx-messenger-clear"}})
						]})
					]}),
					keyboardNode,
					messageReplyNode
				]});
			}
		}
		else if (addBlankNode)
		{
			arMessage = BX.create("div", {attrs : {'id' : 'im-message-'+message.id, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props : { className : "bx-messenger-content-item-text-wrap bx-messenger-item-skipped"}});
		}

		if (arMessage && (!skipAddMessage || addBlankNode))
		{
			var delimiter = null;
			if (lastMessage && lastMessage.getAttribute('data-senderId') != message.senderId)
			{
				delimiter = BX.create("div", {props : { className : "bx-messenger-item-delimiter"}});
			}

			if (appendTop)
			{
				placeholder.insertBefore(arMessage, lastMessage);
				if (delimiter)
				{
					placeholder.insertBefore(delimiter, lastMessage);
				}
			}
			else if (insertBefore && lastMessage && lastMessage.nextElementSibling)
			{
				placeholder.insertBefore(arMessage, lastMessage.nextElementSibling);
				if (delimiter)
				{
					placeholder.insertBefore(delimiter, lastMessage.nextElementSibling);
				}
			}
			else
			{
				if (delimiter)
				{
					placeholder.appendChild(delimiter);
				}
				placeholder.appendChild(arMessage);
			}
		}

		if (
			!customPlace && !addBlankNode && scroll !== false && this.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight)
		)
		{
			if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
			}
			else if (this.BXIM.animationSupport)
			{
				if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
					this.BXIM.messenger.popupMessengerBodyAnimation.stop();

				(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
					duration : 800,
					start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop },
					finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
					step : BX.delegate(function(state){
						this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
					}, this)
				})).animate();
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}

		if (message.params.SENDING == 'Y' || message.params.IS_DELIVERED == 'N')
		{
			this.drawProgessMessage(message.id);
		}

		return messageId;
	};

	MessengerCommon.prototype.drawMessageReply = function(messageId)
	{
		var node = null;
		if (!(this.BXIM.messenger.message[messageId] && this.BXIM.messenger.message[messageId].params && this.BXIM.messenger.message[messageId].params.CHAT_ID > 0))
		{
			return node;
		}

		var chatId = this.BXIM.messenger.message[messageId].params.CHAT_ID;
		var currentChatId = this.BXIM.messenger.message[messageId].chatId;

		var lastMessageDate = this.BXIM.messenger.message[messageId].params.CHAT_LAST_DATE? new Date(this.BXIM.messenger.message[messageId].params.CHAT_LAST_DATE): '';
		var messagesCount = this.BXIM.messenger.message[messageId].params.CHAT_MESSAGE || 0;

		node = BX.create("div", { props : { className : "bx-messenger-content-reply" }, attrs: {id: 'im-message-content-reply-'+messageId, 'data-messageId': messageId, 'data-chatid': chatId}, children: [
			BX.create("span", { props : { className : "bx-messenger-content-reply-block" }, children: [
				BX.create("span", { props : { className : "bx-messenger-content-reply-comment" }, children: [
					BX.create("span", { props : { className : "bx-messenger-content-reply-answer" }, events: {click: BX.delegate(function(){
						this.joinParentChat(BX.proxy_context.getAttribute('data-messageId'), BX.proxy_context.getAttribute('data-chatId'));
					}, this)}, attrs: {'data-messageId': messageId, 'data-chatId': chatId}, html: messagesCount+' ' + BX.Loc.getMessagePlural('IM_R_COMMENT', parseInt(messagesCount))}),
					BX.create("span", { props : { className : "bx-messenger-content-reply-date" }, html: lastMessageDate? ', '+this.formatDate(lastMessageDate): ''}),
				]}),
				BX.create("div", { props : { className : "bx-messenger-content-reply-clear" }})
			]}),
			BX.create("span", { props : { className : "bx-messenger-content-reply-join" }, children: [
				BX.create("span", { props : { className : "bx-messenger-content-reply-join-button" }, html: BX.message('IM_M_OPEN'), events: {click: BX.delegate(function(){
					this.joinParentChat(BX.proxy_context.getAttribute('data-messageId'), BX.proxy_context.getAttribute('data-chatId'));
				}, this)}, attrs: {'data-messageId': messageId, 'data-chatId': chatId}, }),
			]}),
			BX.create("div", { props : { className : "bx-messenger-content-reply-clear" }}),
		]});

		return node;
	}

	MessengerCommon.prototype.joinParentChat = function(messageId, chatId)
	{
		if (!messageId || !chatId)
			return false;

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId])
		{
			this.BXIM.messenger.blockJoinChat[chatId] = false;
			this.BXIM.messenger.openMessenger('chat'+chatId);
		}
		else
		{
			this.BXIM.messenger.blockJoinChat[chatId] = true;

			BX.ajax({
				url: this.BXIM.pathToAjax+'?PARENT_CHAT_JOIN&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				timeout: 60,
				data: {'IM_PARENT_CHAT_JOIN' : 'Y', 'CHAT_ID' : chatId, 'MESSAGE_ID' : messageId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(){
					this.BXIM.messenger.blockJoinChat[chatId] = false;
					this.BXIM.messenger.openMessenger('chat'+chatId);
				}, this),
				onfailure: BX.delegate(function(){
					this.BXIM.messenger.blockJoinChat[chatId] = false;
				}, this)
			});
		}
	};

	MessengerCommon.prototype.openReplyDialog = function(messageId)
	{
		if (this.isMobile())
		{
			alert(BX.message('IM_AV_NEXT_VERSION'));
			return false;
		}

		this.BXIM.messenger.openMessengerPanel();
		this.BXIM.messenger.popupMessengerBodyPanelTitleName.innerHTML = BX.message('IM_R_DIALOG_TITLE');

		var desc = this.drawMessageReply(messageId);
		if (desc)
		{
			this.BXIM.messenger.popupMessengerBodyPanelTitleDesc.innerHTML = '';
			this.BXIM.messenger.popupMessengerBodyPanelTitleDesc.appendChild(desc);
		}
		else
		{
			this.BXIM.messenger.popupMessengerBodyPanelTitleDesc.innerHTML = BX.message('IM_R_COMMENT_ZERO');
		}

		this.BXIM.messenger.popupMessengerBodyPanelWrap.innerHTML = '';
		BX.adjust(this.BXIM.messenger.popupMessengerBodyPanelWrap, {children: [
			this.BXIM.messenger.popupMessengerBodyPanelWrapMessage = BX.create("div", { props : { className : "bx-messenger-body-panel-wrap-message"}}),
			this.BXIM.messenger.popupMessengerBodyPanelWrapMessages = BX.create("div", { props : { className : "bx-messenger-body-panel-wrap-message-list"}}),
			BX.create("div", { props : { className : "bx-messenger-body-panel-wrap-message-textarea"}, children: [
				this.popupMessengerTextareaPlace = BX.create("div", { props : { className : "bx-messenger-textarea-place"}, children : [
					BX.create("div", { props : { className : "bx-messenger-textarea-send" }, children : [
						BX.create("a", {attrs: {href: "#send"}, props : { className : "bx-messenger-textarea-send-button" }, events : { click : BX.delegate(this.sendMessage, this)}}),
					]}),
					this.popupMessengerBodyPanelSmileButton = BX.create("div", {attrs : { title: BX.message('IM_SMILE_MENU')},  props : { className : "bx-messenger-textarea-smile" }, events : { click : BX.delegate(function(e){this.openSmileMenu(); return BX.PreventDefault(e);}, this)}}),
					BX.create("div", { props : { className : "bx-messenger-textarea" }, children : [
						this.popupMessengerPanelTextarea = BX.create("textarea", { props : { value: '', className : "bx-messenger-textarea-input"}}),
						this.popupMessengerPanelTextareaPlaceholder = BX.create("div", { props : {className : "bx-messenger-textarea-placeholder"}, html : BX.message('IM_M_TA_TEXT')})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-clear" }}),
				]})
			]})
		]});

		if (typeof(this.messageGroup) != 'object')
		{
			this.messageGroup = {};
		}
		this.messageGroup['reply'] = {};

		this.drawMessage({
			placeholder: this.BXIM.messenger.popupMessengerBodyPanelWrapMessage,
			placeholderName: 'reply',
			showKeyboard: false,
			showReply: false
		}, this.BXIM.messenger.message[messageId]);

		if (desc)
		{
			this.BXIM.messenger.popupMessengerBodyPanelWrapMessages.innerHTML = '<div class="bx-messenger-content-empty"><span class="bx-messenger-content-load-text">'+BX.message('IM_R_LOAD_COMMENT')+'</span></div>';
		}
		else
		{
			this.BXIM.messenger.popupMessengerBodyPanelWrapMessages.innerHTML = '<div class="bx-messenger-content-empty"><span class="bx-messenger-content-load-text">'+BX.message('IM_R_NO_COMMENT')+'</span></div>';
		}

		this.messageGroup['replyMessages'] = {};
		if (desc)
		{
			setTimeout(BX.delegate(function(){
				//var shuffle = [];
				//for (var i in this.BXIM.messenger.showMessage['776'])
				//	shuffle.push(i);
				if (
					!this.BXIM.messenger.message[messageId]
					|| this.BXIM.messenger.message[messageId].params
					|| this.BXIM.messenger.message[messageId].params.CHAT_ID <= 0
				)
				{
					return false;
				}

				var dialogId = 'chat'+this.BXIM.messenger.message[messageId].params.CHAT_ID;
				this.loadLastMessage(dialogId, BX.delegate(function(dit, result, data){
					if (!result)
					{
						this.BXIM.messenger.popupMessengerBodyPanelWrapMessages.innerHTML = '<div class="bx-messenger-content-empty"><span class="bx-messenger-content-load-text">'+BX.message('IM_F_ERROR')+'</span></div>';
						return false;
					}
					this.BXIM.messenger.popupMessengerBodyPanelWrapMessages.innerHTML = '';
					var shuffle = BX.util.shuffle(this.BXIM.messenger.showMessage[dit]);
					for (var i = 0; i < shuffle.length; i++)
					{
						this.drawMessage({
							placeholder: this.BXIM.messenger.popupMessengerBodyPanelWrapMessages,
							placeholderName: 'replyMessages',
							showKeyboard: false,
							showReply: false,
						}, this.BXIM.messenger.message[shuffle[i]]);
					}
				}, this));

			}, this), 1000)
		}

		return true;
	}

	MessengerCommon.prototype.checkProgessMessage = function()
	{
		for (messageId in this.BXIM.messenger.popupMessengerSendingTimeout)
		{
			if (
				!this.BXIM.messenger.message[messageId] ||
				!this.BXIM.messenger.message[messageId].params ||
				!this.BXIM.messenger.message[messageId].params.SENDING_TS
			)
			{
				delete this.BXIM.messenger.popupMessengerSendingTimeout[messageId];
			}
			else if (
				typeof this.BXIM.messenger.message[messageId].params.FILE_ID !== 'undefined'
				&& this.BXIM.messenger.message[messageId].params.FILE_ID.length > 0
				&& parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+3600 < ((new Date).getTime()/1000)
			)
			{
				this.drawProgessMessage(messageId);
			}
			else if (parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+300 < ((new Date).getTime()/1000))
			{
				this.drawProgessMessage(messageId);
			}
		}
	}

	MessengerCommon.prototype.drawProgessMessage = function(messageId, button)
	{
		var element = BX('im-message-'+messageId);
		if (!element)
			return false;

		BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
		BX.MessengerTimer.start('progressMessage', messageId, 1000, function(id) {
			var element = BX('im-message-'+id);
			if (!element)
				return false;

			BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-start');
		});

		element.parentNode.parentNode.parentNode.previousSibling.innerHTML = '';

		var isDelivered = true;

		var hasParams = this.BXIM.messenger.message[messageId] && this.BXIM.messenger.message[messageId].params;
		var isNotDelivered = this.BXIM.messenger.message[messageId].params.IS_DELIVERED == 'N';
		var isSending = this.BXIM.messenger.message[messageId].params.SENDING == 'Y' && typeof this.BXIM.messenger.message[messageId].params.SENDING_TS !== 'undefined';
		var hasFiles = typeof this.BXIM.messenger.message[messageId].params.FILE_ID === 'object' && this.BXIM.messenger.message[messageId].params.FILE_ID.length > 0;

		if (
			hasParams
			&& (
				isNotDelivered
				|| (
					isSending
					&& (
						hasFiles && parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+3600 < ((new Date).getTime()/1000)
						|| !hasFiles && parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+300 < (new Date()).getTime()/1000
					)
				)
			)
		)
		{
			delete this.BXIM.messenger.popupMessengerSendingTimeout[messageId];
			this.BXIM.messenger.message[messageId].params.IS_DELIVERED = 'N';
			this.BXIM.messenger.message[messageId].params.SENDING = 'N';
			this.BXIM.messenger.message[messageId].params.SENDING_TS = 0;
			isDelivered = false;

			var lastMessageElementDate = BX.findChildByClassName(element.parentNode.parentNode.parentNode, "bx-messenger-content-item-date");
			if (lastMessageElementDate)
				lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');
		}

		if (
			this.BXIM.messenger.message[messageId]
			&& this.BXIM.messenger.message[messageId].params
			&& this.BXIM.messenger.message[messageId].params.SENDING == 'Y'
		)
		{
			this.BXIM.messenger.popupMessengerSendingTimeout[messageId] = this.BXIM.messenger.message[messageId].params.SENDING_TS;
		}

		if (!isDelivered)
		{
			if (this.BXIM.messenger.message[messageId])
			{
				BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
				BX.MessengerCommon.recentListElementStatusError(this.BXIM.messenger.message[messageId].recipientId, messageId);
				BX.MessengerTimer.stop('progressMessage', messageId, true);
			}
		}
		else if (typeof (button) == 'object' || button === true)
		{
			if (this.BXIM.messenger.message[messageId])
			{
				this.BXIM.messenger.errorMessage[this.BXIM.messenger.currentTab] = true;
				BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
				BX.MessengerCommon.recentListElementStatusError(this.BXIM.messenger.message[messageId].recipientId, messageId);
				BX.MessengerTimer.stop('progressMessage', messageId, true);
				button.chat = button.chat? button.chat: (parseInt(this.BXIM.messenger.message[messageId].recipientId) > 0? 'Y':'N');
				BX.adjust(element.parentNode.parentNode.parentNode.previousSibling, {children: [
					BX.create("span", { attrs: { title: button.title? button.title: '', 'data-messageid': messageId, 'data-chat': button.chat }, props : { className : "bx-messenger-content-item-error"}, children:[
						BX.create("span", { props : { className : "bx-messenger-content-item-error-icon"}})
					]})
				]});
			}
			else
			{
				BX.MessengerTimer.stop('progressMessage', messageId, true);
				BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
				BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-start');
				BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
			}
		}
		else
		{
			BX.adjust(element.parentNode.parentNode.parentNode.previousSibling, {children: [
				BX.create("span", { props : { className : "bx-messenger-content-item-progress"}})
			]});
		}

		return true;
	}

	MessengerCommon.prototype.clearProgessMessage = function(messageId)
	{
		delete this.BXIM.messenger.popupMessengerSendingTimeout[messageId];

		var element = BX('im-message-'+messageId);
		if (!element)
			return false;

		if (
			this.BXIM.messenger.message[messageId]
			&& this.BXIM.messenger.message[messageId].params
			&& (
				this.BXIM.messenger.message[messageId].params.SENDING == 'Y'
				|| this.BXIM.messenger.message[messageId].params.IS_DELIVERED == 'N'
			)
		)
		{
			return false;
		}

		BX.MessengerTimer.stop('progressMessage', messageId, true);
		BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
		BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-start');
		BX.removeClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
		element.parentNode.parentNode.parentNode.previousSibling.innerHTML = '';

		return true;
	}



	/* Section: Writing status */
	MessengerCommon.prototype.startWriting = function(userId, dialogId, userName)
	{
		if (dialogId == this.BXIM.userId)
		{
			this.BXIM.messenger.writingList[userId] = true;
			this.drawWriting(userId);

			clearTimeout(this.BXIM.messenger.writingListTimeout[userId]);
			this.BXIM.messenger.writingListTimeout[userId] = setTimeout(BX.delegate(function(){
				this.endWriting(userId);
			}, this), 29500);
		}
		else
		{
			if (!this.BXIM.messenger.writingList[dialogId])
				this.BXIM.messenger.writingList[dialogId] = {};

			if (!this.BXIM.messenger.writingListTimeout[dialogId])
				this.BXIM.messenger.writingListTimeout[dialogId] = {};

			this.BXIM.messenger.writingList[dialogId][userId] = true;
			this.drawWriting(userId, dialogId);

			clearTimeout(this.BXIM.messenger.writingListTimeout[dialogId][userId]);
			this.BXIM.messenger.writingListTimeout[dialogId][userId] = setTimeout(BX.delegate(function(){
				this.endWriting(userId, dialogId);
			}, this), 29500);
		}
	};

	MessengerCommon.prototype.drawWriting = function(userId, dialogId, animation)
	{
		animation = typeof(animation) == 'undefined'? true: animation;
		if (dialogId == this.BXIM.userId)
			return false;

		if (!dialogId || dialogId.toString().substr(0,4) !== 'chat')
		{
			dialogId = '';
		}

		if (this.BXIM.messenger.popupMessenger != null && this.MobileActionEqual('RECENT', 'DIALOG'))
		{
			if (this.BXIM.messenger.writingList[userId] || dialogId && this.countWriting(dialogId) > 0)
			{
				if (BX.MessengerExternalList)
				{
					var elements = BX.MessengerExternalList.getElement((dialogId? dialogId: userId), true);
					if (elements)
					{
						for (var i = 0; i < elements.length; i++)
							BX.addClass(elements[i], 'bx-messenger-cl-status-writing');
					}
				}
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.addClass(elements[i], 'bx-messenger-cl-status-writing');
				}

				if (this.MobileActionEqual('DIALOG') && (this.BXIM.messenger.currentTab == userId || dialogId && this.BXIM.messenger.currentTab == dialogId))
				{
					if (dialogId)
					{
						var userList = [];
						for (var i in this.BXIM.messenger.writingList[dialogId])
						{
							if (this.BXIM.messenger.writingList[dialogId].hasOwnProperty(i) && this.BXIM.messenger.users[i])
							{
								userList.push(this.BXIM.messenger.users[i].name);
							}
						}
						this.drawNotifyMessage(dialogId, 'writing', BX.message('IM_M_WRITING').replace('#USER_NAME#', userList.join(', ')));
					}
					else
					{
						if (!this.isMobile())
						{
							this.BXIM.messenger.popupMessengerPanelAvatar.parentNode.className = 'bx-messenger-panel-avatar bx-messenger-panel-avatar-status-writing';
						}
						this.drawNotifyMessage(userId, 'writing', BX.message('IM_M_WRITING').replace('#USER_NAME#', this.BXIM.messenger.users[userId].name));
					}
				}

			}
			else if (!this.BXIM.messenger.writingList[userId] || dialogId && this.countWriting(dialogId) == 0)
			{
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.removeClass(elements[i], 'bx-messenger-cl-status-writing');
				}
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.removeClass(elements[i], 'bx-messenger-cl-status-writing');
				}

				if (this.MobileActionEqual('DIALOG') && (this.BXIM.messenger.currentTab == userId || this.BXIM.messenger.currentTab == dialogId))
				{
					if (!dialogId)
					{
						if (!this.isMobile())
							this.BXIM.messenger.popupMessengerPanelAvatar.parentNode.className = 'bx-messenger-panel-avatar bx-messenger-panel-avatar-status-' + this.getUserStatus(this.BXIM.messenger.users[userId]);
					}

					var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap? this.BXIM.messenger.popupMessengerBodyWrap.lastChild: null;
					if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify") && this.BXIM.messenger.popupMessengerBody)
					{
						if (!dialogId && this.BXIM.messenger.readedList[userId])
						{
							this.drawReadMessage(userId, this.BXIM.messenger.readedList[userId].messageId, this.BXIM.messenger.readedList[userId].date, false);
						}
						else if (dialogId && this.BXIM.messenger.readedList[dialogId])
						{
							this.drawReadMessageChat(dialogId, false);
						}
						else if (BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight)) // TODO mobile
						{
							if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
							{
								this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
							}
							else if (this.BXIM.animationSupport)
							{
								if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
									this.BXIM.messenger.popupMessengerBodyAnimation.stop();
								(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
									duration : 800,
									start : {scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
									finish : {scroll : this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight},
									transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
									step : BX.delegate(function (state)
									{
										this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
									}, this),
									complete : BX.delegate(function ()
									{
										BX.remove(lastMessage);
									}, this)
								})).animate();
							}
							else if (animation)
							{
								this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight;
								BX.remove(lastMessage);
							}
						}
						else
						{
							this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight;
							BX.remove(lastMessage);
						}
					}
				}
			}
		}
	};

	MessengerCommon.prototype.endWriting = function(userId, dialogId, animation)
	{
		animation = typeof(animation) == 'undefined'? true: animation;
		if (dialogId.toString().substr(0, 4) == 'chat')
		{
			if (this.BXIM.messenger.writingListTimeout[dialogId] && this.BXIM.messenger.writingListTimeout[dialogId][userId])
				clearTimeout(this.BXIM.messenger.writingListTimeout[dialogId][userId]);

			if (this.BXIM.messenger.writingList[dialogId] && this.BXIM.messenger.writingList[dialogId][userId])
				delete this.BXIM.messenger.writingList[dialogId][userId];
		}
		else
		{
			clearTimeout(this.BXIM.messenger.writingListTimeout[userId]);
			delete this.BXIM.messenger.writingList[userId];
		}
		this.drawWriting(userId, dialogId, animation);
	};

	MessengerCommon.prototype.sendWriting = function(dialogId)
	{
		if (!this.BXIM.ppServerStatus || dialogId == 'create' || dialogId == this.BXIM.userId)
			return false;

		if (!this.BXIM.messenger.writingSendList[dialogId])
		{
			clearTimeout(this.BXIM.messenger.writingSendListTimeout[dialogId]);
			this.BXIM.messenger.writingSendList[dialogId] = true;

			var olSilentMode = 'N';
			if (dialogId.toString().substr(0,4) == 'chat' && this.BXIM.messenger.linesSilentMode && this.BXIM.messenger.linesSilentMode[dialogId.toString().substr(4)])
			{
				olSilentMode = 'Y';
			}

			BX.ajax({
				url: this.BXIM.pathToAjax+'?START_WRITING&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_START_WRITING' : 'Y', 'DIALOG_ID' : dialogId, 'OL_SILENT' : olSilentMode, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (data && data.BITRIX_SESSID)
					{
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					}
					if (data.ERROR == 'AUTHORIZE_ERROR' && this.isDesktop() && this.BXIM.messenger.sendAjaxTry < 3)
					{
						this.BXIM.messenger.sendAjaxTry++;
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
					else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
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
			this.BXIM.messenger.writingSendListTimeout[dialogId] = setTimeout(BX.delegate(function(){
				this.endSendWriting(dialogId);
			}, this), 30000);
		}
	};

	MessengerCommon.prototype.endSendWriting = function(dialogId)
	{
		clearTimeout(this.BXIM.messenger.writingSendListTimeout[dialogId]);
		this.BXIM.messenger.writingSendList[dialogId] = false;
	};

	MessengerCommon.prototype.countWriting = function(dialogId)
	{
		var count = 0;
		if (this.BXIM.messenger.writingList[dialogId])
		{
			if (typeof(this.BXIM.messenger.writingList[dialogId]) == 'object')
			{
				for(var i in this.BXIM.messenger.writingList[dialogId])
				{
					if(this.BXIM.messenger.writingList[dialogId].hasOwnProperty(i))
					{
						count++;
					}
				}
			}
			else
			{
				count = 1;
			}
		}

		return count;
	}



	/* Section: Chats */
	MessengerCommon.prototype.leaveFromChat = function(chatId, sendAjax)
	{
		if (!this.BXIM.messenger.chat[chatId])
			return false;

		sendAjax = sendAjax != false;

		if (!sendAjax)
		{
			if (this.BXIM.messenger.chat[chatId].type != 'open' || this.BXIM.messenger.users[this.BXIM.userId].extranet)
			{
				delete this.BXIM.messenger.chat[chatId];
				delete this.BXIM.messenger.userInChat[chatId];
				delete this.BXIM.messenger.unreadMessage['chat'+chatId];
				delete this.BXIM.messenger.showMessage['chat'+chatId];

				if (this.BXIM.messenger.popupMessenger != null)
				{
					if (this.BXIM.messenger.currentTab == 'chat'+chatId)
					{
						this.BXIM.messenger.currentTab = 0;
						this.BXIM.messenger.openChatFlag = false;
						this.BXIM.messenger.openCallFlag = false;
						this.BXIM.messenger.openLinesFlag = false;
						this.BXIM.messenger.extraClose();
					}
				}
			}
			else
			{
				for(var i = 0; i < this.BXIM.messenger.userInChat[chatId].length; i++)
				{
					if (this.BXIM.userId == parseInt(this.BXIM.messenger.userInChat[chatId][i]))
					{
						delete this.BXIM.messenger.userInChat[chatId][i];
						break;
					}
				}
				this.BXIM.messenger.dialogStatusRedraw();
				delete this.BXIM.messenger.unreadMessage['chat'+chatId];
				delete this.BXIM.messenger.showMessage['chat'+chatId];
			}

			this.recentListHide('chat'+chatId, false);
			this.userListRedraw();

			this.BXIM.messenger.updateMessageCount();
			this.BXIM.updateCounter();
		}
		else
		{
			if (BX.MessengerProxy)
			{
				BX.MessengerProxy.sendLeaveChatEvent('chat'+chatId);
			}
			BX.ajax({
				url: this.BXIM.pathToAjax+'?CHAT_LEAVE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_CHAT_LEAVE' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (data.ERROR == '')
					{
						this.readMessage('chat'+data.CHAT_ID, true, false);

						if (
							!this.BXIM.messenger.chat[data.CHAT_ID]
							|| this.BXIM.messenger.chat[data.CHAT_ID].type != 'open'
							|| this.BXIM.messenger.users[this.BXIM.userId].extranet
						)
						{
							delete this.BXIM.messenger.showMessage[data.CHAT_ID];
							delete this.BXIM.messenger.userInChat[data.CHAT_ID];
							delete this.BXIM.messenger.unreadMessage[data.CHAT_ID];
							delete this.BXIM.messenger.chat[data.CHAT_ID];

							if (this.BXIM.messenger.popupMessenger != null)
							{
								if (this.BXIM.messenger.currentTab == 'chat' + data.CHAT_ID)
								{
									this.BXIM.messenger.currentTab = 0;
									this.BXIM.messenger.openChatFlag = false;
									this.BXIM.messenger.openCallFlag = false;
									this.BXIM.messenger.openLinesFlag = false;
									BX.localStorage.set('mct', this.BXIM.messenger.currentTab, 15);
									this.BXIM.messenger.extraClose();
								}
							}
						}
						else
						{
							for(var i = 0; i < this.BXIM.messenger.userInChat[data.CHAT_ID].length; i++)
							{
								if (this.BXIM.userId == parseInt(this.BXIM.messenger.userInChat[data.CHAT_ID][i]))
								{
									delete this.BXIM.messenger.userInChat[data.CHAT_ID][i];
									break;
								}
							}
							delete this.BXIM.messenger.unreadMessage[data.CHAT_ID];
							this.BXIM.messenger.dialogStatusRedraw();
						}

						this.recentListHide('chat'+data.CHAT_ID, false);
						this.userListRedraw();
						BX.localStorage.set('mcl', data.CHAT_ID, 5);

						this.BXIM.messenger.updateMessageCount();
						this.BXIM.updateCounter();
					}
				}, this)
			});
		}
	};

	MessengerCommon.prototype.isSlider = function()
	{
		return location.href.toString().indexOf('SIDE_SLIDER') > 0;
	}

	MessengerCommon.prototype.closeSlider = function()
	{
		if (!this.isSlider())
		{
			return false;
		}

		BX.SidePanel.Instance.close();

		return true;
	}

	MessengerCommon.prototype.reloadDialogOL = function()
	{
		for (var chatId in this.BXIM.messenger.chat)
		{
			if(this.BXIM.messenger.chat.hasOwnProperty(chatId))
			{
				if(
					typeof(chatId) != "undefined" &&
					this.BXIM.messenger.chat[chatId].type === "lines"
				)
				{
					delete this.BXIM.messenger.userInChat[chatId];
					delete this.BXIM.messenger.unreadMessage['chat'+chatId];
					delete this.BXIM.messenger.showMessage['chat'+chatId];
				}
			}
		}
	}

	MessengerCommon.prototype.dialogCloseCurrent = function(close)
	{
		if (this.closeSlider())
		{
			return true;
		}

		this.BXIM.messenger.currentTab = 0;
		this.BXIM.messenger.openChatFlag = false;
		this.BXIM.messenger.openCallFlag = false;
		this.BXIM.messenger.openLinesFlag = false;
		this.BXIM.messenger.extraClose();
	}

	/* Section: Pull Events */
	MessengerCommon.prototype.pullEvent = function()
	{
		//return false; // TODO disable pull;

		if (typeof BX.PULL === 'undefined' || !this.BXIM.ppServerStatus)
		{
			return false;
		}

		var pullHandler = BX.delegate(function(command,params,extra)
		{
			if (this.isMobile())
			{
				this.BXIM.checkRevision(extra.revision_im_mobile);
			}
			else
			{
				this.BXIM.checkRevision(extra.revision_im_web);
			}

			if (command == 'generalChatId')
			{
				this.BXIM.messenger.generalChatId = params.id;
			}
			else if (command == 'generalChatAccess')
			{
				if (this.BXIM.messenger.canSendMessageGeneralChat && params.status == 'blocked')
				{
					if (this.MobileActionEqual('DIALOG'))
					{
						this.BXIM.messenger.canSendMessageGeneralChat = false;
						if (this.isMobile())
						{
							this.BXIM.messenger.dialogStatusRedrawDelay();
						}
						else
						{
							this.BXIM.messenger.redrawChatHeader({userRedraw: false});
						}
					}
				}
				else if (this.isMobile() && this.MobileActionEqual('DIALOG'))
				{
					console.log('NOTICE: Window reload, because CHANGE ALLOW OPTIONS for general chat');
					location.reload();
				}
				else if (this.isDesktop())
				{
					console.log('NOTICE: Window reload, because CHANGE ALLOW OPTIONS for general chat');
					location.reload();
				}
			}
			else if (command == 'settingsUpdate')
			{
				for (var i in params)
				{
					this.BXIM.settings[i] = params[i];
				}
			}
			else if (command == 'desktopOffline')
			{
				this.BXIM.desktopStatus = false;
			}
			else if (command == 'desktopOnline')
			{
				this.BXIM.desktopStatus = true;
				this.BXIM.desktopVersion = params.version;

				var result = document.title.match(/^(\((\d+)\)\s)(.*)+/);
				if (result && result[1])
				{
					document.title = document.title.substr(result[1].length);
				}
			}
			else if (command == 'readMessage')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.skipReadMessage = false;

				this.readMessage(params.dialogId, false, false, true);

				this.BXIM.dialogDetailCounter[params.dialogId] = params.counter;

				this.recentListUpdateItem({
					id: params.dialogId,
					counter: params.counter,
				});

				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'readMessageChat')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.readMessage(params.dialogId, false, false, true);

				if (params.lines)
				{
					this.BXIM.linesDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}
				else
				{
					this.BXIM.dialogDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}

				this.recentListUpdateItem({
					id: params.dialogId,
					counter: params.counter,
				});
				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'unreadMessage' || command == 'unreadMessageChat' )
			{
				if (params.lines)
				{
					this.BXIM.linesDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}
				else
				{
					this.BXIM.dialogDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}

				this.recentListUpdateItem({
					id: params.dialogId,
					counter: params.counter,
				});
				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'readAllChats')
			{
				this.BXIM.messenger.recent.forEach(function(element) {
					element.counter = 0;
				});
				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'readMessageChatOpponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				if (!this.BXIM.messenger.readedList['chat'+params.chatId])
				{
					this.BXIM.messenger.readedList['chat'+params.chatId] = {};
				}

				this.BXIM.messenger.readedList['chat'+params.chatId][params.userId] = {
					'messageId' : params.lastId,
					'date' : new Date(params.date)
				};

				this.recentListElementStatusChange(params.dialogId, params.chatMessageStatus);
				this.drawReadMessageChat('chat'+params.chatId);
			}
			else if (command == 'readMessageOpponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.drawReadMessage(params.userId, params.lastId, new Date(params.date));

				if (typeof(this.BXIM.messenger.users[params.userId]) != 'undefined')
				{
					this.BXIM.messenger.users[params.userId].idle = false;
					this.BXIM.messenger.users[params.userId].last_activity_date = new Date();

					if (this.BXIM.messenger.currentTab.toString() == params.userId.toString())
					{
						var getLastDate = BX.MessengerCommon.getUserLastDate(this.BXIM.messenger.users[params.userId]);
						if (this.isMobile())
						{
							BXMobileApp.UI.Page.TopBar.title.setDetailText(getLastDate);
						}
						else if (this.BXIM.messenger.popupMessengerPanelLastDate)
						{
							this.BXIM.messenger.popupMessengerPanelLastDate.innerHTML = getLastDate? '. '+getLastDate: '';
						}
					}
				}
				if (typeof(this.BXIM.messenger.users[params.userId]) != 'undefined')
				{
					this.BXIM.messenger.users[params.userId].idle = false;
					this.BXIM.messenger.users[params.userId].last_activity_date = new Date();

					if (this.BXIM.messenger.currentTab.toString() == params.userId.toString())
					{
						var getLastDate = BX.MessengerCommon.getUserLastDate(this.BXIM.messenger.users[params.userId]);
						if (this.isMobile())
						{
							BXMobileApp.UI.Page.TopBar.title.setDetailText(getLastDate);
						}
						else if (this.BXIM.messenger.popupMessengerPanelLastDate)
						{
							this.BXIM.messenger.popupMessengerPanelLastDate.innerHTML = getLastDate? '. '+getLastDate: '';
						}
					}
				}

				this.recentListElementStatusChange(params.dialogId, params.chatMessageStatus);
			}
			else if (command == 'unreadMessageOpponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap? this.BXIM.messenger.popupMessengerBodyWrap.lastChild: null;
				if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify"))
				{
					if (params.userId == this.BXIM.messenger.currentTab || !this.countWriting(this.BXIM.messenger.currentTab))
					{
						BX.remove(lastMessage);
					}
				}
				if (typeof(this.BXIM.messenger.users[params.userId]) != 'undefined')
				{
					this.BXIM.messenger.users[params.userId].idle = false;
					this.BXIM.messenger.users[params.userId].last_activity_date = new Date();

					if (this.BXIM.messenger.currentTab.toString() == params.userId.toString())
					{
						var getLastDate = BX.MessengerCommon.getUserLastDate(this.BXIM.messenger.users[params.userId]);
						if (this.isMobile())
						{
							BXMobileApp.UI.Page.TopBar.title.setDetailText(getLastDate);
						}
						else if (this.BXIM.messenger.popupMessengerPanelLastDate)
						{
							this.BXIM.messenger.popupMessengerPanelLastDate.innerHTML = getLastDate? '. '+getLastDate: '';
						}
					}
				}

				this.recentListElementStatusChange(params.dialogId, params.chatMessageStatus);
			}
			else if (command == 'unreadMessageChatOpponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				if (!this.BXIM.messenger.readedList['chat'+params.chatId])
				{
					this.BXIM.messenger.readedList['chat'+params.chatId] = {};
				}
				delete this.BXIM.messenger.readedList['chat'+params.chatId][params.userId];
				this.drawReadMessageChat('chat'+params.chatId);

				if (typeof(this.BXIM.messenger.users[params.userId]) != 'undefined')
				{
					this.BXIM.messenger.users[params.userId].idle = false;
					this.BXIM.messenger.users[params.userId].last_activity_date = new Date();

					if (this.BXIM.messenger.currentTab.toString() == params.userId.toString())
					{
						var getLastDate = BX.MessengerCommon.getUserLastDate(this.BXIM.messenger.users[params.userId]);
						if (this.isMobile())
						{
							BXMobileApp.UI.Page.TopBar.title.setDetailText(getLastDate);
						}
						else if (this.BXIM.messenger.popupMessengerPanelLastDate)
						{
							this.BXIM.messenger.popupMessengerPanelLastDate.innerHTML = getLastDate? '. '+getLastDate: '';
						}
					}
				}
				this.recentListElementStatusChange(params.dialogId, params.chatMessageStatus);
			}
			else if (command == 'startWriting')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				if (this.isBot(params.userId) && !params.DEFERRED && this.BXIM.messenger.showMessage[params.dialogId] && this.BXIM.messenger.showMessage[params.dialogId].length)
				{
					var bot = this.BXIM.messenger.bot[params.userId];
					if (bot.type == 'human')
					{
						var deferredPull = BX.clone({'command': command,'params': params,'extra': extra});
						setTimeout(BX.delegate(function(){
							deferredPull.params.DEFERRED = true;
							if (this.isMobile())
							{
								BX.onCustomEvent(window, "onPull-im", [deferredPull]);
							}
							else
							{
								BX.onCustomEvent(window, "onPullEvent-im", [deferredPull.command, deferredPull.params, deferredPull.extra]);
							}
						}, this), 1000);

						return false;
					}
				}

				if (typeof(this.BXIM.messenger.users[params.userId]) != 'undefined')
				{
					this.BXIM.messenger.users[params.userId].idle = false;
					this.BXIM.messenger.users[params.userId].last_activity_date = new Date();

					if (this.BXIM.messenger.currentTab.toString() == params.userId.toString())
					{
						var getLastDate = BX.MessengerCommon.getUserLastDate(this.BXIM.messenger.users[params.userId]);
						if (this.isMobile())
						{
							BXMobileApp.UI.Page.TopBar.title.setDetailText(getLastDate);
						}
						else if (this.BXIM.messenger.popupMessengerPanelLastDate)
						{
							this.BXIM.messenger.popupMessengerPanelLastDate.innerHTML = getLastDate? '. '+getLastDate: '';
						}
					}
				}

				this.startWriting(params.userId, params.dialogId, params.userName);
			}
			else if (command == 'message' || command == 'messageChat')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				if (
					!params.deferred
					&& this.BXIM.ppStatus
					&& !this.BXIM.ppServerStatus
					&& this.BXIM.lastRecordId >= params.message.id
				)
				{
					return false;
				}

				if (params.message.senderId != this.BXIM.userId)
				{
					BX.onCustomEvent('onImMessageReceive', [{command: command, params: params}]);
				}

				if (this.sendBotCommandBlock[params.message.senderId])
				{
					for (var messageId in this.sendBotCommandBlock[params.message.senderId])
					{
						delete this.sendBotCommandBlock[params.message.senderId][messageId];
						var messageKeyboardBox = BX('im-message-keyboard-'+messageId);
						if (messageKeyboardBox)
						{
							var nodesButton = BX.findChildrenByClassName(messageKeyboardBox, "bx-messenger-keyboard-button-block", false);
							for (var i = 0; i < nodesButton.length; i++)
							{
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-progress");
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-block");
							}
						}
					}
				}

				if (this.isBot(params.message.senderId) && !params.deferred && this.BXIM.messenger.showMessage[params.dialogId] && this.BXIM.messenger.showMessage[params.dialogId].length)
				{
					var bot = this.BXIM.messenger.bot[params.message.senderId];
					if (bot.type == 'human')
					{
						if (params.chat[params.dialogId] && params.chat[params.dialogId].entity_type == 'LINES')
						{
							waitTime = 1000;
						}
						else
						{
							var waitTime = (params.message.text.split(" ").length*300)+1000;
							if (waitTime > 5000)
							{
								waitTime = 5000;
							}
						}

						var deferredPull = BX.clone({'command': command,'params': params,'extra': extra, 'waitTime': waitTime});
						setTimeout(BX.delegate(function(){
							deferredPull.params.deferred = true;
							if (this.isMobile())
							{
								BX.onCustomEvent(window, "onPull-im", [deferredPull]);
							}
							else
							{
								BX.onCustomEvent(window, "onPullEvent-im", [deferredPull.command, deferredPull.params, deferredPull.extra]);
							}
						}, this), waitTime);

						return false;
					}
				}

				//Delete 'writing' message from OL
				if (params.chatId && this.BXIM.messenger.linesWritingList[params.chatId])
				{
					var prevMessageId = this.BXIM.messenger.linesWritingList[params.chatId].id;
					var prevMessage = BX.findChildByClassName(
						this.BXIM.messenger.popupMessengerBodyWrap,
						"bx-messenger-content-item-id-"+prevMessageId
					);
					if (prevMessage)
					{
						BX.remove(prevMessage);
						delete this.BXIM.messenger.linesWritingList[params.chatId];
					}
				}

				var data = {};
				data.SHOW_NEW_MESSAGE = !(params.message.params && params.message.params.NOTIFY === 'N');
				data.MESSAGE = {};
				data.USERS_MESSAGE = {};

				params.message.date = new Date(params.message.date);

				for (var i in params.chat)
				{
					params.chat[i].date_create = new Date(params.chat[i].date_create);
					this.BXIM.messenger.chat[i] = params.chat[i];
				}
				for (var i in params.userInChat)
				{
					this.BXIM.messenger.userInChat[i] = params.userInChat[i];
				}
				for (var i in params.userBlockChat)
				{
					this.BXIM.messenger.userChatBlockStatus[i] = params.userBlockChat[i];
				}
				var userChangeStatus = {};
				for (var i in params.users)
				{
					if (
						this.BXIM.messenger.users[i]
						&& this.BXIM.messenger.users[i].status != params.users[i].status
						&& Math.round(params.message.date.getTime()/1000)+180 > Math.round(new Date()/1000)
					)
					{
						userChangeStatus[i] = this.BXIM.messenger.users[i].status;
						this.BXIM.messenger.users[i].status = params.users[i].status;
					}
				}
				if (this.MobileActionEqual('RECENT'))
				{
					for (var i in userChangeStatus)
					{
						if (!this.BXIM.messenger.users[i])
							continue;

						var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+i);
						if (elements != null)
						{
							for (var j = 0; j < elements.length; j++)
							{
								var userStatus = BX.MessengerCommon.getUserStatus(this.BXIM.messenger.users[i]);
								BX.removeClass(elements[j], 'bx-messenger-cl-status-' + userChangeStatus[i]);
								BX.addClass(elements[j], 'bx-messenger-cl-status-' + userStatus);
								elements[j].setAttribute('data-status', userStatus);
							}
						}
						var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+i);
						if (elements != null)
						{
							for (var j = 0; j < elements.length; j++)
							{
								var userStatus = BX.MessengerCommon.getUserStatus(this.BXIM.messenger.users[i]);
								BX.removeClass(elements[j], 'bx-messenger-cl-status-' + userChangeStatus[i]);
								BX.addClass(elements[j], 'bx-messenger-cl-status-' + userStatus);
								elements[j].setAttribute('data-status', userStatus);
							}
						}
					}
				}
				elements = null;
				data.USERS = params.users;

				if (this.MobileActionEqual('DIALOG'))
				{
					for (var i in params.files)
					{
						if (!this.BXIM.disk.files[params.chatId])
							this.BXIM.disk.files[params.chatId] = {};
						if (this.BXIM.disk.files[params.chatId][i])
							continue;
						params.files[i].date = new Date(params.files[i].date);
						this.BXIM.disk.files[params.chatId][i] = params.files[i];
					}
				}

				if (
					(params.message.templateFileId || params.message.templateId)
					&& params.chatId &&
					this.BXIM.messenger.message[params.message.templateId]
				)
				{
					this.clearProgessMessage(params.message.templateId);

					if (BX('im-message-' + params.message.templateId))
					{
						BX('im-message-' + params.message.templateId).id = 'im-message-' + params.message.id;
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, { attribute: { 'data-messageid': '' + params.message.templateId } }, true);
						if (element)
						{
							element.setAttribute('data-messageid', '' + params.message.id + '');
							if (element.getAttribute('data-blockmessageid') == '' + params.message.templateId)
							{
								element.setAttribute('data-blockmessageid', '' + params.message.id + '');
							}
							BX.removeClass(element, 'bx-messenger-content-item-id-' + params.message.templateId);
							BX.addClass(element, 'bx-messenger-content-item-id-' + params.message.id);
							BX.removeClass(element, 'bx-messenger-content-item-content-progress');
						}
						else
						{
							var element2 = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, { attribute: { 'data-blockmessageid': '' + params.message.templateId } }, true);
							if (element2)
							{
								element2.setAttribute('data-blockmessageid', '' + params.message.id + '');
							}
						}
					}

					var messageKeyboardBox = BX('im-message-keyboard-'+params.message.templateId);
					if (messageKeyboardBox)
					{
						messageKeyboardBox.id = 'im-message-keyboard-'+params.message.id;
					}
					else
					{
						messageKeyboardBox = BX('im-message-keyboard-empty-'+params.message.templateId);
						if (messageKeyboardBox)
						{
							messageKeyboardBox.id = 'im-message-keyboard-empty-'+params.message.id;
						}
					}

					this.BXIM.messenger.message[params.message.id] = params.message;
					delete this.BXIM.messenger.message[params.message.templateId];

					if (!this.BXIM.messenger.showMessage[params.dialogId])
					{
						this.BXIM.messenger.showMessage[params.dialogId] = [];
					}

					this.BXIM.messenger.showMessage[params.dialogId] = this.BXIM.messenger.showMessage[params.dialogId].filter(function(element) {
						return element != params.message.templateId && element != params.message.id;
					});
					this.BXIM.messenger.showMessage[params.dialogId].push(params.message.id.toString());

					if (params.message.templateFileId)
					{
						this.BXIM.disk.files[params.chatId][params.message.templateFileId] = params.files[params.message.params.FILE_ID[0]];
						this.diskRedrawFile(params.chatId, params.message.templateFileId);
					}

					var messageBox = BX('im-message-'+params.message.id);
					messageBox.innerHTML = this.prepareText(params.message.text, true, true, true);

					if (params.message.params)
					{
						if (params.message.params.URL_ONLY == 'Y' && this.BXIM.settings.enableRichLink)
						{
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
						}

						if (params.message.params.LARGE_FONT == 'Y' && this.BXIM.settings.enableBigSmile)
						{
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
						}
						else
						{
							BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
						}
						if (params.message.params.ATTACH)
						{
							var attachNode = BX.MessengerCommon.drawAttach(params.message.id, this.BXIM.messenger.message[params.message.id].chatId, params.message.params.ATTACH);
							if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
							{
								messageBox.nextElementSibling.innerHTML = '';
								if (attachNode.length > 0)
								{
									BX.adjust(messageBox.nextElementSibling, {children: attachNode});
								}
							}
							else if (attachNode.length > 0)
							{
								attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"}, children : attachNode});
								if (messageBox.nextElementSibling)
								{
									messageBox.parentNode.insertBefore(attachNode, messageBox.nextElementSibling);
								}
								else
								{
									messageBox.parentNode.appendChild(attachNode);
								}
							}
						}
					}
				}
				else
				{
					data.MESSAGE[params.message.id] = params.message;
				}

				this.BXIM.lastRecordId = parseInt(params.message.id) > this.BXIM.lastRecordId? parseInt(params.message.id): this.BXIM.lastRecordId;

				var messageText = params.message.text;
				if (!messageText || messageText.length <= 0)
				{
					if (params.message.params && params.message.params.FILE_ID && params.message.params.FILE_ID.length > 0)
					{
						messageText = '['+BX.message('IM_F_FILE')+']';
					}
					else if (params.message.params && params.message.params.ATTACH && params.message.params.ATTACH.length > 0)
					{
						messageText = '['+BX.message('IM_F_ATTACH')+']';
					}
					else
					{
						messageText = BX.message('IM_M_DELETED');
					}
				}

				if (params.message.senderId == this.BXIM.userId)
				{
					if (this.isMobile())
					{
						if (params.message.params['FILE_ID'] && params.message.params['FILE_ID'].length > 0)
						{
							var skipMessageDraw = false;
							params.message.params['FILE_ID'].forEach(function(fileId){
								if (this.BXIM.disk.messageBlock[fileId])
								{
									delete this.BXIM.disk.messageBlock[fileId];
									skipMessageDraw = true;
								}
							}.bind(this));
							if (skipMessageDraw)
							{
								return ;
							}
						}
					}

					this.readMessage(params.message.recipientId, false, false);

					data.USERS_MESSAGE[params.message.recipientId] = [params.message.id];

					this.updateStateVar(data);

					var lines = params.lines || null;
					if (lines)
					{
						params.lines.date_create = new Date(params.lines.date_create);
					}

					this.recentListAddItem({
						id: params.dialogId,
						chat_id: params.chatId,
						counter: params.counter,
						lines: params.lines,
						message: {
							id: params.message.id,
							date: params.message.date,
							author_id: params.message.senderId,
							status: 'received',
							text: BX.util.htmlspecialchars(params.message.textOriginal),
							attach: params.message.params && params.message.params.ATTACH? params.message.params.ATTACH.length > 0: false,
							file: params.message.params && params.message.params.FILE_ID? params.message.params.FILE_ID.length > 0: false,
						},
					});
					this.recentListRedraw();
					this.BXIM.messenger.updateMessageCount();
				}
				else
				{
					data.UNREAD_MESSAGE = {};
					data.UNREAD_MESSAGE[params.dialogId] = [params.message.id];
					data.USERS_MESSAGE[params.dialogId] = [params.message.id];

					if (command == 'message')
						this.endWriting(params.message.senderId, 0, false);
					else
						this.endWriting(params.message.senderId, params.message.recipientId, false);

					var externalListMessage = null;
					if (typeof params.message.params.CODE !== 'undefined')
					{
						if (
							params.message.params.CODE === 'USER_JOIN'
							&& BX.MessengerExternalList
							&& BX.MessengerExternalList.canShowMessage(params.dialogId)
						)
						{
							data.SHOW_NEW_MESSAGE = false;
							externalListMessage = {
								dialogId: params.dialogId,
								title: BX.util.htmlspecialcharsback(params.users[params.dialogId].name),
								text: params.message.text
							};
						}
						else if (
							params.message.params.CODE === 'USER_JOIN_GENERAL'
							&& BX.MessengerExternalList
							&& BX.MessengerExternalList.canShowMessage(params.message.senderId)
						)
						{
							data.SHOW_NEW_MESSAGE = false;
							externalListMessage = {
								dialogId: params.dialogId,
								title: BX.util.htmlspecialcharsback(params.users[params.message.senderId].name),
								text: params.message.text
							};
						}
					}

					this.updateStateVar(data);

					if (command == 'messageChat' && !BX.MessengerCommon.userInChat(params.message.chatId))
					{
						if (this.isMobile())
						{
							var isLines = this.BXIM.currentTab.toString().substr(0,4) === 'chat'
								&& this.BXIM.messenger.chat[this.BXIM.currentTab.substr(4)]
								&& this.BXIM.messenger.chat[this.BXIM.currentTab.substr(4)].type === 'lines';
							if (isLines)
							{
								BX.MessengerCommon.hideLinesKeyboard();
							}
						}

						return ;
					}

					var lines = params.lines || null;
					if (lines)
					{
						params.lines.date_create = new Date(params.lines.date_create);
					}

					this.recentListAddItem({
						id: params.dialogId,
						chat_id: params.chatId,
						counter: params.counter,
						lines: lines,
						message: {
							id: params.message.id,
							date: params.message.date,
							author_id: params.message.senderId,
							status: 'delivered',
							text: BX.util.htmlspecialchars(params.message.textOriginal),
							attach: params.message.params && params.message.params.ATTACH? params.message.params.ATTACH.length > 0: false,
							file: params.message.params && params.message.params.FILE_ID? params.message.params.FILE_ID.length > 0: false,
						},
					});
					this.recentListRedraw();
					this.BXIM.messenger.updateMessageCount();

					if (externalListMessage && BX.MessengerExternalList)
					{
						BX.MessengerExternalList.showMessage(externalListMessage);
					}

					if (
						this.BXIM.messenger.currentTab == params.dialogId
						&& this.BXIM.isFocus()
					)
					{
						this.readMessage(params.dialogId, true, true);
					}
				}
			}
			else if (command == 'messageDeleteComplete')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (!this.BXIM.messenger.message[params.id])
					return false;

				var dialogId = 0;
				if (params.type == 'private')
				{
					dialogId = params.fromUserId == this.BXIM.userId && params.toUserId? params.toUserId: params.fromUserId;
					this.endWriting(dialogId, 0, false);
				}
				else
				{
					dialogId = 'chat' + params.chatId;
					this.endWriting(params.senderId, dialogId, false);
				}

				if (this.BXIM.messenger.currentTab == dialogId && BX('im-message-'+params.id))
				{
					var messageWrap = BX('im-message-'+params.id).parentNode.parentNode.parentNode.parentNode.parentNode;
					if (messageWrap.getAttribute('data-messageId') == messageWrap.getAttribute('data-blockMessageId'))
					{
						BX.remove(messageWrap);
					}
					else
					{
						messageWrap = BX('im-message-'+params.id).parentNode;
						if (messageWrap.nextSibling && BX.hasClass(messageWrap.nextSibling, 'bx-messenger-hr'))
						{
							BX.remove(messageWrap.nextSibling);
						}
						else if (!messageWrap.nextSibling && BX.hasClass(messageWrap.previousSibling, 'bx-messenger-hr'))
						{
							BX.remove(messageWrap.previousSibling);
						}
						BX.remove(messageWrap);
					}
				}
				this.recentListElementUpdate(dialogId, params.id, params.text, params.counter, params.muted);
				if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
					this.recentListRedraw();

				delete this.BXIM.messenger.message[params.id];
				this.BXIM.messenger.showMessage[dialogId].sort(BX.delegate(function(i, ii) {if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = this.BXIM.messenger.message[i].date.getTime(); var i2 = this.BXIM.messenger.message[ii].date.getTime(); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
			}
			else if (command == 'messageUpdate' || command == 'messageDelete')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				for (var botId in this.sendBotCommandBlock)
				{
					if (this.sendBotCommandBlock[botId][params.id])
					{
						delete this.sendBotCommandBlock[botId][params.id];
						var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
						if (messageKeyboardBox)
						{
							var nodesButton = BX.findChildrenByClassName(messageKeyboardBox, "bx-messenger-keyboard-button-block", false);
							for (var i = 0; i < nodesButton.length; i++)
							{
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-progress");
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-block");
							}
						}
					}
				}

				if (this.BXIM.messenger.message[params.id])
				{
					if (!this.BXIM.messenger.message[params.id].params)
						this.BXIM.messenger.message[params.id].params = {};

					var dialogId = 0;
					if (command == 'messageDelete')
					{
						params.text = BX.message('IM_M_DELETED');
						if (!this.BXIM.messenger.message[params.id].params)
						{
							this.BXIM.messenger.message[params.id].params = {};
						}
						this.BXIM.messenger.message[params.id].params.IS_DELETED = 'Y';
					}
					else if (command == 'messageUpdate')
					{
						this.BXIM.messenger.message[params.id].params = params.params;
					}

					this.BXIM.messenger.message[params.id].text = BX.MessengerCommon.prepareText(params.text, true, true, true);
					this.BXIM.messenger.message[params.id].textOriginal = params.text;

					if (params.type == 'private')
					{
						dialogId = params.fromUserId == this.BXIM.userId && params.toUserId? params.toUserId: params.fromUserId;
						this.endWriting(dialogId, 0, false);
					}
					else
					{
						dialogId = 'chat' + params.chatId;
						this.endWriting(params.senderId, dialogId, false);
					}

					this.recentListElementUpdate(dialogId, params.id, params.text);

					if (this.BXIM.messenger.currentTab == dialogId && BX('im-message-'+params.id))
					{
						var messageBox = BX('im-message-'+params.id);

						if (params.params && params.params.IS_EDITED == 'Y')
						{
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-message-edited');
						}

						var textAlreadyUpdated = false;

						if (command == 'messageDelete')
						{
							BX.addClass(messageBox.parentNode, 'bx-messenger-message-deleted');
							var keyboadBox = BX('im-message-keyboard-'+params.id);
							BX.remove(keyboadBox);
							BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
						}
						else if (command == 'messageUpdate')
						{
							if (params.params)
							{
								if (params.params.DATE_TEXT)
								{
									var newText = this.replaceDateText(params.id, this.BXIM.messenger.message[params.id].text, params.params);
									messageBox.innerHTML = this.prepareText(newText, false, true, true);
									textAlreadyUpdated = true;
								}
								if (params.params.IS_EDITED == 'Y')
								{
									BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-without-padding');
								}
								if (params.params.URL_ONLY == 'Y' && this.BXIM.settings.enableRichLink)
								{
									BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
								}
								else
								{
									BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
								}
								if (params.params.LARGE_FONT == 'Y' && this.BXIM.settings.enableBigSmile)
								{
									BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
								}
								else
								{
									BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
								}
								if (params.params.ATTACH)
								{
									var attachNode = BX.MessengerCommon.drawAttach(params.id, this.BXIM.messenger.message[params.id].chatId, params.params.ATTACH);
									if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
									{
										messageBox.nextElementSibling.innerHTML = '';
										if (attachNode.length > 0)
										{
											BX.adjust(messageBox.nextElementSibling, {children: attachNode});
										}
									}
									else if (attachNode.length > 0)
									{
										attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"}, children : attachNode});
										if (messageBox.nextElementSibling)
										{
											messageBox.parentNode.insertBefore(attachNode, messageBox.nextElementSibling);
										}
										else
										{
											messageBox.parentNode.appendChild(attachNode);
										}
									}
								}
								if (params.params.KEYBOARD)
								{
									var keyboardNode = BX.MessengerCommon.drawKeyboard(this.BXIM.messenger.currentTab, params.id, params.params.KEYBOARD);

									var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
									if (!messageKeyboardBox)
									{
										messageKeyboardBox = BX('im-message-keyboard-empty-'+params.id);
										messageKeyboardBox.id = 'im-message-keyboard-'+params.id;
										messageKeyboardBox.className = 'bx-messenger-keyboard';
									}
									if (messageKeyboardBox)
									{
										messageKeyboardBox.innerHTML = keyboardNode? keyboardNode.innerHTML: "";
									}
								}
							}
							else if (typeof(params.params) != 'undefined' && params.params == '')
							{
								if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
								{
									BX.remove(messageBox.nextElementSibling);
								}
							}
						}

						if (!textAlreadyUpdated)
						{
							messageBox.innerHTML = BX.MessengerCommon.prepareText(this.BXIM.messenger.message[params.id].text, true, true, true);
						}

						BX.addClass(messageBox, 'bx-messenger-message-edited-anim');
						if (
							messageBox.previousSibling
							&& (
								BX.hasClass(messageBox.previousSibling, 'bx-messenger-file-box')
								|| params.params && params.params.ATTACH
							)
						)
						{
							BX.addClass(messageBox.previousSibling, 'bx-messenger-file-box-with-message');
						}
						setTimeout(BX.delegate(function(){
							BX.removeClass(messageBox, 'bx-messenger-message-edited-anim');
						}, this), 1000);
					}

					if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
						this.recentListRedraw();
				}
			}
			else if (command == 'messageParamsUpdate')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				if (!this.BXIM.messenger.message[params.id])
					return false;

				if (this.BXIM.messenger.message[params.id].params && this.BXIM.messenger.message[params.id].params.IS_DELETED == 'Y')
					return false;

				var animation = typeof(params.animation) == 'undefined'? null: params.animation;

				for (var botId in this.sendBotCommandBlock)
				{
					if (this.sendBotCommandBlock[botId][params.id])
					{
						delete this.sendBotCommandBlock[botId][params.id];
						var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
						if (messageKeyboardBox)
						{
							var nodesButton = BX.findChildrenByClassName(messageKeyboardBox, "bx-messenger-keyboard-button-block", false);
							for (var i = 0; i < nodesButton.length; i++)
							{
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-progress");
								BX.removeClass(nodesButton[i], "bx-messenger-keyboard-button-block");
							}
						}
					}
				}

				this.BXIM.messenger.message[params.id].params = params.params;

				if (params.type == 'private')
				{
					dialogId = params.fromUserId == this.BXIM.userId? params.toUserId: params.fromUserId;
				}
				else
				{
					dialogId = 'chat' + params.chatId;
				}

				var messageBox = BX('im-message-'+params.id);
				if (this.BXIM.messenger.currentTab == dialogId && messageBox)
				{
					var messageFullBox = messageBox.parentNode.parentNode.parentNode.parentNode.parentNode;
					if (params.params)
					{
						if (params.params.DATE_TEXT)
						{
							var newText = this.replaceDateText(params.id, this.BXIM.messenger.message[params.id].text, params.params);
							messageBox.innerHTML = this.prepareText(newText, false, true, true);
						}

						if (params.params.FILE_ID)
						{
							var filesNode = BX.MessengerCommon.diskDrawFiles(this.BXIM.messenger.message[params.id].chatId, params.params.FILE_ID);
							if (messageBox.previousElementSibling && BX.hasClass(messageBox.previousElementSibling, 'bx-messenger-file-box'))
							{
								messageBox.previousElementSibling.innerHTML = '';
								if (filesNode.length > 0)
								{
									BX.adjust(messageBox.previousElementSibling, {children: filesNode});
								}
							}
							else if (filesNode.length > 0)
							{
								var filesNodeWithText = params.text != '' || params.params && params.params.ATTACH;
								filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(filesNodeWithText? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
								if (messageBox.previousElementSibling)
								{
									messageBox.parentNode.insertBefore(filesNode, messageBox.previousElementSibling);
								}
								else
								{
									messageBox.parentNode.insertBefore(filesNode, messageBox);
								}
							}

							if ((messageBox.innerHTML != '' || params.params && params.params.ATTACH) && messageBox.previousElementSibling && BX.hasClass(messageBox.previousElementSibling, 'bx-messenger-file-box'))
							{
								BX.addClass(messageBox.previousElementSibling, 'bx-messenger-file-box-with-message');
							}
						}
						if (params.params.ATTACH)
						{
							var attachNode = BX.MessengerCommon.drawAttach(params.id, this.BXIM.messenger.message[params.id].chatId, params.params.ATTACH);
							if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
							{
								messageBox.nextElementSibling.innerHTML = '';
								if (attachNode.length > 0)
								{
									BX.adjust(messageBox.nextElementSibling, {children: attachNode});
								}
							}
							else if (attachNode.length > 0)
							{
								attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"}, children : attachNode});
								if (messageBox.nextElementSibling)
								{
									messageBox.parentNode.insertBefore(attachNode, messageBox.nextElementSibling);
								}
								else
								{
									messageBox.parentNode.appendChild(attachNode);
								}
							}
							if (animation != 'N')
							{
								animation = 'Y';
							}
						}
						if (params.params.KEYBOARD)
						{
							var keyboardNode = BX.MessengerCommon.drawKeyboard(this.BXIM.messenger.currentTab, params.id, params.params.KEYBOARD);

							var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
							if (!messageKeyboardBox)
							{
								messageKeyboardBox = BX('im-message-keyboard-empty-'+params.id);
								messageKeyboardBox.id = 'im-message-keyboard-'+params.id;
								messageKeyboardBox.className = 'bx-messenger-keyboard';
							}
							if (messageKeyboardBox)
							{
								messageKeyboardBox.innerHTML = keyboardNode? keyboardNode.innerHTML: "";
							}
							if (animation != 'N')
							{
								animation = 'Y';
							}
						}
						if (params.params.CHAT_USER || params.params.CHAT_ID || params.params.CHAT_MESSAGE || params.params.CHAT_LAST_DATE)
						{
							var messageContentReplyBox = BX('im-message-content-reply-'+params.id);
							var contentReplyNode = BX.MessengerCommon.drawMessageReply(params.id);
							if (messageContentReplyBox)
							{
								messageContentReplyBox.innerHTML = contentReplyNode? contentReplyNode.innerHTML: "";
							}
						}

						if (params.params && params.params.URL_ONLY == 'Y' && this.BXIM.settings.enableRichLink)
						{
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
						}
						else if (params.params && params.params.URL_ONLY == 'N')
						{
							BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
						}

						if (params.params && params.params.LARGE_FONT == 'Y' && this.BXIM.settings.enableBigSmile)
						{
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
						}
						else if (params.params && params.params.LARGE_FONT == 'N')
						{
							BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-large-font');
						}

						if (params.params && params.params.IS_EDITED == 'Y')
						{
							BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-without-padding');
							BX.addClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-message-edited');
							if (animation != 'N')
							{
								animation = 'Y';
							}
						}
					}
					else if (typeof(params.params) != 'undefined' && params.params == '')
					{
						if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
						{
							BX.remove(messageBox.nextElementSibling);
							if (animation != 'N')
							{
								animation = 'Y';
							}
						}
					}
					if (params.params && typeof(params.params.CLASS) != 'undefined')
					{
						var messageParentBox = BX.findParent(messageBox, {className: 'bx-messenger-content-item'});
						BX.addClass(messageParentBox, params.params.CLASS);
					}
					if (params.params && params.params.IS_DELIVERED)
					{
						if (params.params.IS_DELIVERED == 'N')
						{
							this.drawProgessMessage(params.id);
						}
						else
						{
							this.clearProgessMessage(params.id);
						}
					}
					if (params.params && params.params.SENDING)
					{
						if (params.params.SENDING == 'Y')
						{
							this.drawProgessMessage(params.id);
						}
						else
						{
							this.clearProgessMessage(params.id);
						}
					}
					if (params.params.IMOL_SID && parseInt(params.params.IMOL_SID) > 0)
					{
						var extraBox = BX.findChildByClassName(messageFullBox, "bx-messenger-message-extra");
						if (!extraBox)
						{
							messageFullBox.insertBefore(BX.create("div", {
								props : { className : "bx-messenger-message-extra"},
								html: BX.message('IM_OL_DIALOG_NUMBER').replace("#NUMBER#", params.params.IMOL_SID)}
							), messageFullBox.firstChild);

							if (this.isElementVisibleOnScreen(messageFullBox, BXIM.messenger.popupMessengerBody))
							{
								this.linesBodyScroll()
							}
						}
					}
					if (params.params.IMOL_FORM && this.BXIM.messenger.chat[params.chatId] && this.BXIM.messenger.chat[params.chatId].type == 'livechat')
					{
						var delay = params.params.IMOL_FORM.toString().substr(-6) == '-delay';
						var formType = delay? params.params.IMOL_FORM.substr(0, params.params.IMOL_FORM.lastIndexOf('-delay')): params.params.IMOL_FORM;

						if (this.BXIM.messenger.popupMessengerLiveChatDelayedFormMid < params.id && this.BXIM.messenger.popupMessengerLiveChatFormType != formType)
						{
							this.BXIM.messenger.popupMessengerLiveChatDelayedFormMid = params.id;
							this.BXIM.messenger.linesLivechatFormHide();

							clearTimeout(this.BXIM.messenger.popupMessengerLiveChatActionTimeout);
							this.BXIM.messenger.popupMessengerLiveChatActionTimeout = setTimeout(BX.delegate(function() {
								this.BXIM.messenger.linesLivechatFormShow(formType);
							}, this), delay? 30000: 5000);
						}
					}
					if (params.params.IMOL_VOTE && messageBox)
					{
						var voteNode = this.linesVoteDraw(params.id);
						if (voteNode)
						{
							BX.cleanNode(messageBox);
							messageBox.appendChild(voteNode);
						}
						if (animation != 'N')
						{
							animation = 'Y';
						}
					}
					else if (typeof(params.params.IMOL_VOTE_SID) != 'undefined' && messageBox)
					{
						var messageText = BX.findChildByClassName(messageBox, "bx-messenger-content-item-vote-message-text");
						if (messageText)
						{
							var voteNode = this.linesVoteResultDraw(params.id, messageText.innerHTML);
							if (voteNode)
							{
								BX.cleanNode(messageBox);
								messageBox.appendChild(voteNode);
							}
						}
					}

					if (animation == 'Y')
					{
						BX.addClass(messageBox, 'bx-messenger-message-edited-anim');
						setTimeout(BX.delegate(function(){
							BX.removeClass(messageBox, 'bx-messenger-message-edited-anim');
						}, this), 1000);
					}
				}
			}
			else if (command == 'messageLike')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				var iLikeThis = BX.util.in_array(this.BXIM.userId, params.users);
				var likeCount = params.users.length > 0? params.users.length: '';

				if  (!this.BXIM.messenger.message[params.id])
				{
					return false;
				}

				if (typeof(this.BXIM.messenger.message[params.id].params) != 'object')
				{
					this.BXIM.messenger.message[params.id].params = {};
				}

				this.BXIM.messenger.message[params.id].params.LIKE = params.users;

				if (BX('im-message-'+params.id))
				{
					var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+params.id+''}}, false);
					if (element)
					{
						var elementLike = BX.findChildByClassName(element, "bx-messenger-content-item-like");
						if (elementLike)
						{
							var elementLikeDigit = BX.findChildByClassName(elementLike, "bx-messenger-content-like-digit", false);
							var elementLikeButton = BX.findChildByClassName(elementLike, "bx-messenger-content-like-button", false);

							if (iLikeThis)
							{
								BX.addClass(elementLike, 'bx-messenger-content-item-liked');
							}
							else
							{
								BX.removeClass(elementLike, 'bx-messenger-content-item-liked');
							}

							if (likeCount>0)
							{
								elementLikeDigit.setAttribute('title', BX.message('IM_MESSAGE_LIKE_LIST'));
								BX.removeClass(elementLikeDigit.parentNode, 'bx-messenger-content-like-digit-off');
							}
							else
							{
								elementLikeDigit.setAttribute('title', '');
								BX.addClass(elementLikeDigit.parentNode, 'bx-messenger-content-like-digit-off');
							}

							if (elementLikeDigit.innerHTML < likeCount)
							{
								var elementContent = BX.findChildByClassName(element, "bx-messenger-content-item-content", false);
								BX.addClass(elementContent, 'bx-messenger-content-item-plus-like');
								setTimeout(function(){
									BX.removeClass(elementContent, 'bx-messenger-content-item-plus-like');
								}, 500);
							}
							elementLikeDigit.innerHTML = likeCount;
						}
					}
				}
			}
			else if (command == 'promotionRead')
			{
				if (BX.MessengerPromo)
				{
					BX.MessengerPromo.read(params.id);
				}
			}
			else if (command == 'fileUpload')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				if (this.BXIM.disk.filesProgress[params.fileTmpId])
					return false;

				params.fileParams.date = new Date(params.fileParams.date);
				if (this.BXIM.disk.files[params.fileChatId] && this.BXIM.disk.files[params.fileChatId][params.fileId])
				{
					params.fileParams['preview'] = this.BXIM.disk.files[params.fileChatId][params.fileId]['preview'];
				}
				if (!this.BXIM.disk.files[params.fileChatId])
					this.BXIM.disk.files[params.fileChatId] = {};

				this.BXIM.disk.files[params.fileChatId][params.fileId] = params.fileParams;
				this.diskRedrawFile(params.fileChatId, params.fileId);

				if (this.BXIM.messenger.popupMessengerBody && BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight))
				{
					if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
					}
					else if (this.BXIM.animationSupport)
					{
						if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
							this.BXIM.messenger.popupMessengerBodyAnimation.stop();
						(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
							duration : 800,
							start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop },
							finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
							transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
							step : BX.delegate(function(state){
								this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
							}, this)
						})).animate();
					}
					else if (this.BXIM.messenger.popupMessengerBody)
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
					}
				}
			}
			else if (command == 'fileUnRegister')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				for (var id in params.files)
				{
					if (this.BXIM.disk.filesRegister[params.chatId])
					{
						delete this.BXIM.disk.filesRegister[params.chatId][params.files[id]];
					}
					if (this.BXIM.disk.files[params.chatId] && this.BXIM.disk.files[params.chatId][params.files[id]])
					{
						this.BXIM.disk.files[params.chatId][params.files[id]].status = 'error';
						BX.MessengerCommon.diskRedrawFile(params.chatId, params.files[id]);
					}
					delete this.BXIM.disk.filesProgress[id];
				}
				this.drawTab(this.getRecipientByChatId(params.chatId));
			}
			else if (command == 'fileDelete')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				delete this.BXIM.disk.files[params.chatId][params.fileId];

				this.drawTab(this.getRecipientByChatId(params.chatId));
			}
			else if (command == 'dialogChange')
			{
				if (!this.BXIM.isOpen() || !this.BXIM.isFocus())
				{
					return false;
				}

				this.BXIM.openMessenger(params.dialogId);
			}
			else if (command == 'chatRename')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId].name = BX.util.htmlspecialchars(params.name);
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatAvatar')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;
				this.BXIM.messenger.updateChatAvatar(params.chatId, params.avatar);
			}
			else if (command == 'chatChangeColor')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId].color = params.color;
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatHide')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				this.recentListHide(params.dialogId, false);

				if (!this.isMobile() && params.dialogId == this.BXIM.messenger.currentTab)
				{
					BX.MessengerCommon.dialogCloseCurrent();
				}

				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'chatShow')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
				{
					return false;
				}

				var recent = this.recentListElementFormat(params);

				delete this.BXIM.messenger.showMessage[recent.id];
				delete this.BXIM.messenger.history[recent.id];

				if (!this.isMobile() && params.id == this.BXIM.messenger.currentTab)
				{
					this.BXIM.messenger.openMessenger(params.id);
				}

				this.recentListAddItem(recent);
				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();

				var hasActiveSharing = BX.MessengerCalls && BX.MessengerCalls.hasActiveSharing();

				if (
					this.BXIM.settings.status != 'dnd'
					&& this.BXIM.notify.muteModeCode <= 0
					&& !hasActiveSharing
					&& recent.message.id > 0
					&& !this.BXIM.messenger.message[recent.message.id]
					&& recent.counter > 0
				)
				{
					this.BXIM.messenger.message[recent.message.id] = {
						id: recent.message.id,
						chatId: recent.chat_id,
						date: recent.message.date,
						messageType: (recent.type === 'user'? 'P': (recent.lines? 'L': 'C')),
						params: {},
						recipientId: recent.id,
						senderId: recent.message.author_id,
						text: recent.message.text,
						textOriginal: recent.message.text,
						fake: true,
					};
					if (!this.BXIM.messenger.flashMessage[recent.id])
					{
						this.BXIM.messenger.flashMessage[recent.id] = {};
					}
					this.BXIM.messenger.flashMessage[recent.id][recent.message.id] = true;
					this.BXIM.messenger.newMessage();
				}
			}
			else if (command == 'chatMuteNotify')
			{
				if (params.lines)
				{
					this.BXIM.linesDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}
				else
				{
					this.BXIM.dialogDetailCounter[params.dialogId] = params.muted? 0: params.counter;
				}

				this.BXIM.messenger.updateMessageCount();
				this.muteMessageChat(params.dialogId, params.mute, false);
			}
			else if (command == 'chatPin')
			{
				if (this.MobileActionNotEqual('RECENT'))
					return false;

				this.recentListElementPin(params.dialogId, params.active);
			}
			else if (command == 'chatUnread')
			{
				if (params.lines)
				{
					this.BXIM.linesDetailCounter[params.dialogId] = params.muted? 0: (params.counter? params.counter: 1);
				}
				else
				{
					this.BXIM.dialogDetailCounter[params.dialogId] = params.muted? 0: (params.counter? params.counter: 1);
				}

				this.recentListUpdateItem({
					id: params.dialogId,
					unread: params.active,
				});
				this.recentListRedraw();
				this.BXIM.messenger.updateMessageCount();
			}
			else if (command == 'chatUserAdd')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				for (var i in params.users)
				{
					params.users[i].last_activity_date = params.users[i].last_activity_date? new Date(params.users[i].last_activity_date): false;
					params.users[i].mobile_last_date = params.users[i].mobile_last_date? new Date(params.users[i].mobile_last_date): false;
					params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
					params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

					this.BXIM.messenger.users[i] = params.users[i];
				}

				if (!this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId] = {'id': params.chatId, 'name': params.chatId, 'owner': params.chatOwner, 'extranet': params.chatExtranet, 'fake': true, date_create: ''};
				}
				else
				{
					this.BXIM.messenger.chat[params.chatId].extranet = params.chatExtranet;
					if (this.BXIM.messenger.userInChat[params.chatId])
					{
						for (i = 0; i < params.newUsers.length; i++)
							this.BXIM.messenger.userInChat[params.chatId].push(params.newUsers[i]);
					}
					else
						this.BXIM.messenger.userInChat[params.chatId] = params.newUsers;

					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatOwner')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (!this.BXIM.messenger.chat[params.chatId])
					return false;

				this.BXIM.messenger.chat[params.chatId].owner = params.userId;

				if (
					!this.isMobile()
					&& this.BXIM.messenger.currentTab == 'chat'+params.chatId
				)
				{
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatManagers')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (!this.BXIM.messenger.chat[params.chatId])
					return false;

				this.BXIM.messenger.chat[params.chatId].manager_list = params.list;

				if (this.BXIM.messenger.currentTab == params.dialogId)
				{
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatUserLeave')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (params.userId == this.BXIM.userId)
				{
					this.readMessage('chat'+params.chatId, true, false, true);
					this.leaveFromChat(params.chatId, false);
				}
				else if (this.MobileActionEqual('DIALOG'))
				{
					if (!this.BXIM.messenger.chat[params.chatId] || !this.BXIM.messenger.userInChat[params.chatId])
						return false;

					var newStack = [];
					for (var i = 0; i < this.BXIM.messenger.userInChat[params.chatId].length; i++)
						if (this.BXIM.messenger.userInChat[params.chatId][i] != params.userId)
							newStack.push(this.BXIM.messenger.userInChat[params.chatId][i]);

					this.BXIM.messenger.userInChat[params.chatId] = newStack;
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatUpdateParams')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (!this.BXIM.messenger.chat[params.chatId])
					return false;

				for (var name in params.params)
				{
					if (!params.params.hasOwnProperty(name))
					{
						continue;
					}

					this.BXIM.messenger.chat[params.chatId][name] = params.params[name];

					if (
						name == 'entity_data_1'
						&& this.BXIM.messenger.chat[params.chatId].type == 'livechat'
					)
					{
						var session = this.livechatGetSession(params.chatId);
						session.readedTime = session.readedTime? new Date(session.readedTime): false;
						this.drawReadMessage('chat'+params.chatId, session.readedId, session.readedTime);

						if (session.showForm == 'N')
						{
							if (!this.BXIM.messenger.popupMessengerLiveChatLastSend || this.BXIM.messenger.popupMessengerLiveChatLastSend+1000 < +(new Date()))
							{
								this.BXIM.messenger.linesLivechatFormHide();
							}
						}
					}
				}

				if (this.BXIM.messenger.currentTab == params.dialogId)
				{
					this.BXIM.messenger.redrawChatHeader();
				}

				if (this.MobileActionEqual('RECENT') && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
				{
					this.recentListRedraw();
				}
			}
			else if (command == 'botAdd' || command == 'botUpdate')
			{
				if (this.BXIM.userExtranet)
					return false;

				this.BXIM.messenger.bot[params.bot.id] = params.bot;

				params.user.last_activity_date = params.user.last_activity_date? new Date(params.user.last_activity_date): false;
				params.user.mobile_last_date = params.user.mobile_last_date? new Date(params.user.mobile_last_date): false;
				params.user.idle = params.user.idle? new Date(params.user.idle): false;
				params.user.absent = params.user.absent? new Date(params.user.absent): false;

				this.BXIM.messenger.users[params.user.id] = params.user;


				if (typeof(params.userInGroup) != "undefined")
				{
					for (var i in params.userInGroup)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = params.userInGroup[i];
						}
						else
						{
							for (var j = 0; j < params.userInGroup[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(params.userInGroup[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}
				}
			}
			else if (command == 'botDelete')
			{
				if (this.BXIM.messenger.bot[params.botId])
				{
					delete this.BXIM.messenger.bot[params.botId];
				}
				if (this.BXIM.messenger.users[params.botId])
				{
					delete this.BXIM.messenger.users[params.botId];
				}
				this.recentListHide(params.botId, false);

				if (this.BXIM.messenger.currentTab == params.botId)
				{
					this.BXIM.messenger.openMessenger('general');
				}
			}
			else if (command == 'userInvite')
			{
				if (!this.BXIM.settings.viewCommonUsers)
				{
					return false;
				}
				this.BXIM.messenger.users[params.user.id] = params.user;

				this.recentListAddItem({
					id: params.user.id,
					invited: params.invited,
					message: {text: ''}
				});

				this.recentListRedraw();
			}
			else if (command == 'userUpdate' || command == 'updateUser')
			{
				params.user.last_activity_date = params.user.last_activity_date? new Date(params.user.last_activity_date): false;
				params.user.mobile_last_date = params.user.mobile_last_date? new Date(params.user.mobile_last_date): false;
				params.user.idle = params.user.idle? new Date(params.user.idle): false;
				params.user.absent = params.user.absent? new Date(params.user.absent): false;

				this.BXIM.messenger.users[params.user.id] = params.user;
				this.BXIM.messenger.redrawChatHeader();
			}
			else if (command == 'notifyAdd')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				params.date = new Date(params.date);
				params.text = BX.MessengerCommon.prepareText(params.text, true, true, false);

				var data = {};
				data.UNREAD_NOTIFY = {};
				data.UNREAD_NOTIFY[params.id] = [params.id];
				this.BXIM.messenger.notify.notify[params.id] = params;

				if (
					this.BXIM.ppStatus && !this.BXIM.ppServerStatus
					&& this.BXIM.lastRecordId >= params.message.id
				)
				{
					this.BXIM.messenger.notify.flashNotify[params.id] = false;
				}
				else
				{
					this.BXIM.messenger.notify.flashNotify[params.id] = params.silent != 'Y';
				}

				if (params.settingName == "im|like" && params.originalTag.substr(0,10) == "RATING|IM|")
				{
					var messageParams = params.originalTag.split("|");
					if (this.BXIM.messenger.message[messageParams[4]] && this.BXIM.messenger.message[messageParams[4]].recipientId == this.BXIM.messenger.currentTab && this.BXIM.windowFocus)
					{
						delete data.UNREAD_NOTIFY[params.id];
						this.BXIM.notify.flashNotify[params.id] = false;
					}
				}
				this.BXIM.notify.changeUnreadNotify(data.UNREAD_NOTIFY, true, params.silent == 'N');

				this.BXIM.lastRecordId = parseInt(params.id) > this.BXIM.lastRecordId? parseInt(params.id): this.BXIM.lastRecordId;
			}
			else if (command == 'notifyRead')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				this.BXIM.notify.initNotifyCount = params.counter;
				this.BXIM.notify.notifyCount = params.counter;

				params.list.forEach(function(id){
					delete this.BXIM.notify.unreadNotify[id];
				}.bind(this));

				this.BXIM.notify.viewNotifyMarkupUpdate();
				this.BXIM.notify.updateNotifyCount(false);
			}
			else if (command == 'notifyConfirm')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				var notifyId = parseInt(params.id);
				if (this.BXIM.notify.notify[notifyId])
				{
					if (this.isMobile())
					{
						delete this.BXIM.notify.notify[notifyId];
					}
					else
					{
						this.BXIM.notify.notify[notifyId].confirmMessages = params.confirmMessages;
					}
				}
				delete this.BXIM.notify.unreadNotify[notifyId];
				delete this.BXIM.notify.flashNotify[notifyId];
				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.notifyOpen)
					this.BXIM.notify.openNotify(true);
			}
			else if (command == 'notifyUnread')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				params.list.forEach(function(id){
					this.BXIM.notify.viewNotify(id, false, false);
				}.bind(this));
			}
			else if (command == 'commandDelete')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				for (var i = 0; i < this.BXIM.messenger.command.length; i++)
				{
					if (!this.BXIM.messenger.command[i] || this.BXIM.messenger.command[i].id != params.commandId)
					{
						continue;
					}

					delete this.BXIM.messenger.command[i];

					if (this.commandPopup != null)
					{
						this.commandPopup.destroy();
					}

					break;
				}
			}
			else if (command == 'appDeleteIcon')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				for (var i = 0; i < this.BXIM.messenger.textareaIcon.length; i++)
				{
					if (!this.BXIM.messenger.textareaIcon[i] || this.BXIM.messenger.textareaIcon[i].id != params.iconId)
					{
						continue;
					}

					delete this.BXIM.messenger.textareaIcon[i];

					if (this.popupSmileMenu != null)
					{
						this.popupSmileMenu.destroy();
					}

					BX.MessengerSupport24.closePopup();

					var element = BX.findChildByClassName(this.BXIM.messenger.popupMessengerTextareaIconBox, 'bx-messenger-textarea-icon-marketplace-'+params.iconId, true);
					if (element)
					{
						BX.remove(element);
					}

					break;
				}
			}
			else if (command == 'appUpdateIcon')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				for (var i = 0; i < this.BXIM.messenger.textareaIcon.length; i++)
				{
					if (!this.BXIM.messenger.textareaIcon[i] || this.BXIM.messenger.textareaIcon[i].id != params.iconId)
					{
						continue;
					}

					if (params.context)
					{
						this.BXIM.messenger.textareaIcon[i].context = params.context;
					}
					if (params.js)
					{
						this.BXIM.messenger.textareaIcon[i].js = params.js;
					}
					if (params.iframe)
					{
						this.BXIM.messenger.textareaIcon[i].iframe = params.iframe;
					}
					if (params.iframeWidth)
					{
						this.BXIM.messenger.textareaIcon[i].iframeWidth = params.iframeWidth;
					}
					if (params.iframeHeight)
					{
						this.BXIM.messenger.textareaIcon[i].iframeHeight = params.iframeHeight;
					}

					if (params.userId != this.BXIM.userId && this.popupSmileMenu != null)
					{
						this.popupIframeMenu.destroy();
					}

					break;
				}
			}
		}, this);

		var pullOnlineHandler = BX.delegate(function(command, params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
			}
			if (command == 'list' || command == 'userStatus')
			{
				var userList = [];
				for (var i in params.users)
				{
					userList.push(params.users[i]);
				}
				this.userChangeStatus(userList);
			}

		}, this);

		var pullOpenLinesHandler = BX.delegate(function(command, params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
			}

			if (command == 'linesAnswer')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (!this.BXIM.messenger.chat[params.chatId])
					return false;

				this.BXIM.messenger.chat[params.chatId].owner = this.BXIM.userId;
				this.BXIM.messenger.redrawChatHeader();

				if (this.BXIM.messenger.popupMessengerTextarea)
				{
					this.BXIM.messenger.popupMessengerTextarea.focus();
				}
			}
			else if (command == 'queueItemUpdate')
			{
				if (typeof(this.BXIM.messenger.openlines) == 'undefined')
				{
					this.BXIM.messenger.openlines.queue = [params];
				}
				else
				{
					var push = true;
					for (var i=0,len=this.BXIM.messenger.openlines.queue.length; i<len; i++)
					{
						if (this.BXIM.messenger.openlines.queue[i].id == params.id)
						{
							this.BXIM.messenger.openlines.queue[i].name = params.name;
							this.BXIM.messenger.openlines.queue[i].priority = params.priority;
							this.BXIM.messenger.openlines.queue[i].queue_type = params.queue_type;
							push = false;
							break;
						}
					}
					if (push)
					{
						this.BXIM.messenger.openlines.queue.push(params);
					}
				}
			}
			else if (command == 'queueItemDelete')
			{
				if (typeof(this.BXIM.messenger.openlines) == 'undefined' || this.BXIM.messenger.openlines.queue.length <= 0)
					return true;

				var newQueue = [];
				for (var i=0,len=this.BXIM.messenger.openlines.queue.length; i<len; i++)
				{
					if (this.BXIM.messenger.openlines.queue[i].id != params.id)
					{
						newQueue.push(this.BXIM.messenger.openlines.queue[i]);
					}
				}
				this.BXIM.messenger.openlines.queue = newQueue;
			}
			else if (command == 'updateSessionStatus')
			{
				this.recentListUpdateItem({
					id: 'chat'+params.chatId,
					lines: { status: params.status }
				});
				this.recentListRedraw();
			}
		}, this);

		if(this.isMobile())
		{
			console.warn("MOBILE!")
			BXMobileApp.addCustomEvent("onPull-im",
				BX.delegate(function(dataObject)
				{
					console.log(dataObject);
					var commandList = dataObject.data;

					if( typeof (commandList) == "undefined" )
					{
						//backward compatibility
						pullHandler(dataObject["command"],dataObject["params"],dataObject["extra"]);
					}
					else
					{
						for (var i = 0; i < commandList.length; i++)
						{
							pullHandler(commandList[i]["command"],commandList[i]["params"],commandList[i]["extra"]);
						}
					}
				},this)
			)

			BXMobileApp.addCustomEvent("onPullOnline", pullOnlineHandler);
			BXMobileApp.addCustomEvent("onPull-imopenlines", pullOpenLinesHandler);
		}
		else
		{
			BX.addCustomEvent("onPullOnlineEvent", pullOnlineHandler);
			BX.addCustomEvent("onPullEvent-im", pullHandler);
			BX.addCustomEvent("onPullEvent-imopenlines", pullOpenLinesHandler);
		}

		BX.PULL.subscribe({
			type: 'client',
			moduleId: 'imopenlines',
			command: 'linesMessageWrite', callback: function(params, sender) {
				//Check md5 of incoming message (sessionId, chatId, userId)
				if (
					!this.BXIM.messenger.chat[params.operatorChatId]
					|| !this.BXIM.messenger.chat[params.operatorChatId].entity_id
				)
				{
					return;
				}

				var operatorChatId = this.BXIM.messenger.chat[params.operatorChatId].id;
				var chatName = 'chat' + operatorChatId;

				var sessionId = 0;
				var session = BX.MessengerCommon.linesGetSession(this.BXIM.messenger.chat[params.operatorChatId]);
				if (session)
				{
					sessionId = session.id;
				}

				var source = this.BXIM.messenger.chat[params.operatorChatId].entity_id.toString().split('|');

				var clientChatId = 0;
				var clientId = 0;
				if (source[2] && source[3])
				{
					clientChatId = source[2];
					clientId = source[3];
				}

				var infoString = BX.md5(
					sessionId
					+ '/' + clientChatId
					+ '/' + clientId
				);
				if (params.infoString === infoString)
				{
					if (this.BXIM.messenger.linesWritingList[operatorChatId])
					{
						this.BXIM.messenger.linesWritingList[operatorChatId].text = params.text;
						var prevMessageId = this.BXIM.messenger.linesWritingList[operatorChatId].id;
						var prevMessage = BX.findChildByClassName(
							this.BXIM.messenger.popupMessengerBodyWrap,
							"bx-messenger-content-item-id-"+prevMessageId
						);
						if (prevMessage)
						{
							if (params.text === '')
							{
								clearTimeout(this.BXIM.messenger.linesWritingListTimeout[operatorChatId]);
								BX.remove(prevMessage);
								delete this.BXIM.messenger.linesWritingList[operatorChatId];
								BX.MessengerCommon.endWriting(clientId, chatName);
							}
							else
							{
								var prevMessageContent = BX('im-message-'+prevMessageId);
								prevMessageContent.innerText = params.text;

								clearTimeout(this.BXIM.messenger.linesWritingListTimeout[operatorChatId]);
								this.BXIM.messenger.linesWritingListTimeout[operatorChatId] = setTimeout(BX.delegate(function(){
									BX.remove(prevMessage);
									delete this.BXIM.messenger.linesWritingList[operatorChatId];
									BX.MessengerCommon.endWriting(clientId, chatName);
								}, this), 29500);
							}
						}
					}
					else
					{
						if (params.text === '')
						{
							return;
						}

						var message = {
							id: 'ol-writing-' + Date.now(),
							senderId: clientId,
							text: BX.util.htmlspecialchars(params.text),
							date: new Date(),
							params: {
								CLASS: "bx-messenger-content-item-lines-writing"
							}
						};

						this.BXIM.messenger.linesWritingList[operatorChatId] = message;

						if (chatName !== BXIM.messenger.currentTab)
						{
							return;
						}
						BX.MessengerCommon.drawMessage(BXIM.messenger.currentTab, message);
						BX.MessengerCommon.startWriting(clientId, chatName);


						var createdMessage = BX.findChildByClassName(
							this.BXIM.messenger.popupMessengerBodyWrap,
							"bx-messenger-content-item-id-"+message.id
						);

						clearTimeout(this.BXIM.messenger.linesWritingListTimeout[operatorChatId]);
						this.BXIM.messenger.linesWritingListTimeout[operatorChatId] = setTimeout(BX.delegate(function(){
							BX.remove(createdMessage);
							delete this.BXIM.messenger.linesWritingList[operatorChatId];
							BX.MessengerCommon.endWriting(clientId, chatName);
						}, this), 29500);
					}
				}
			}
		});
	}


	/* Section: Fetch messages */
	MessengerCommon.prototype.updateStateVar = function(data, send, writeMessage)
	{
		writeMessage = writeMessage !== false;
		if (typeof(data.CHAT) != "undefined")
		{
			for (var i in data.CHAT)
			{
				data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
				this.BXIM.messenger.chat[i] = data.CHAT[i];
			}
		}
		if (typeof(data.USER_IN_CHAT) != "undefined")
		{
			for (var i in data.USER_IN_CHAT)
			{
				this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
			}
		}
		if (typeof(data.USER_BLOCK_CHAT) != "undefined")
		{
			for (var i in data.USER_BLOCK_CHAT)
			{
				this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
			}
		}
		if (typeof(data.USERS) != "undefined")
		{
			for (var i in data.USERS)
			{
				data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
				data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
				data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
				data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

				this.BXIM.messenger.users[i] = data.USERS[i];
			}
		}
		if (typeof(data.USER_IN_GROUP) != "undefined")
		{
			for (var i in data.USER_IN_GROUP)
			{
				if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
				{
					this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
				}
				else
				{
					for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
						this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

					this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
				}
			}
		}

		if (typeof data.UNREAD_MESSAGE === 'undefined')
		{
			data.UNREAD_MESSAGE = {};
		}

		if (typeof(data.MESSAGE) != "undefined")
		{
			for (var i in data.MESSAGE)
			{
				if (this.BXIM.messenger.message[i] && this.BXIM.messenger.message[i].dropDuplicate)
				{
					data.MESSAGE[i].dropDuplicate = true;
				}

				data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
				data.MESSAGE[i].textOriginal = data.MESSAGE[i].text;
				data.MESSAGE[i].text = BX.MessengerCommon.prepareText(data.MESSAGE[i].text, true, true, true);

				this.BXIM.messenger.message[i] = data.MESSAGE[i];
				this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
			}
		}

		this.changeUnreadMessage(data.UNREAD_MESSAGE, !!data.SHOW_NEW_MESSAGE);

		if (typeof(data.USERS_MESSAGE) != "undefined")
		{
			for (var i in data.USERS_MESSAGE)
			{
				data.USERS_MESSAGE[i].sort(BX.delegate(function(i, ii) {i = parseInt(i); ii = parseInt(ii); if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = this.BXIM.messenger.message[i].date.getTime(); var i2 = this.BXIM.messenger.message[ii].date.getTime(); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
				for (var j = 0; j < data.USERS_MESSAGE[i].length; j++)
				{
					if (!data.USERS_MESSAGE[i][j])
						continue;

					data.USERS_MESSAGE[i][j] = data.USERS_MESSAGE[i][j].toString();

					if (
						this.BXIM.messenger.message[data.USERS_MESSAGE[i][j]].dropDuplicate
						|| !this.BXIM.messenger.showMessage[i]
						|| !BX.util.in_array(data.USERS_MESSAGE[i][j], this.BXIM.messenger.showMessage[i])
					)
					{
						if (!this.BXIM.messenger.showMessage[i])
						{
							this.BXIM.messenger.showMessage[i] = [];
						}

						this.BXIM.messenger.showMessage[i] = this.BXIM.messenger.showMessage[i].filter(function(element) {
							return element != data.USERS_MESSAGE[i][j];
						});
						this.BXIM.messenger.showMessage[i].push(data.USERS_MESSAGE[i][j].toString());

						if (!this.BXIM.messenger.history[i])
						{
							this.BXIM.messenger.history[i] = [];
						}
						this.BXIM.messenger.history[i] = BX.util.array_merge(this.BXIM.messenger.history[i], data.USERS_MESSAGE[i]);

						if (
							writeMessage
							&& this.BXIM.messenger.currentTab == i
							&& this.MobileActionEqual('DIALOG')
							&& !BX('im-message-' + data.USERS_MESSAGE[i][j])
						)
						{
							this.drawMessage(i, this.BXIM.messenger.message[data.USERS_MESSAGE[i][j]]);
						}
					}
				}
			}
		}
	};

	MessengerCommon.prototype.changeUnreadMessage = function(unreadMessage, showNewMessage)
	{
		if (BX.type.isArray(unreadMessage))
		{
			return;
		}

		var userStatus = this.isMobile()? 'online': this.BXIM.settings.status;

		for (var i in unreadMessage)
		{
			if (this.BXIM.messenger.unreadMessage[i])
				this.BXIM.messenger.unreadMessage[i] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[i], unreadMessage[i]));
			else
				this.BXIM.messenger.unreadMessage[i] = unreadMessage[i];

			this.BXIM.messenger.unreadMessage[i].sort(function(a, b) {return a-b;});

			// if (
			// 	this.BXIM.messenger.popupMessenger != null
			// 	&& this.BXIM.messenger.currentTab == i
			// 	&& this.BXIM.isFocus()
			// )
			// {
			// 	this.readMessage(i, true, true);
			// }
			// else
			if (this.isMobile() && this.BXIM.messenger.currentTab == i)
			{
				var dialogId = this.BXIM.messenger.currentTab;
				this.BXIM.isFocusMobile(BX.delegate(function(visible){
					if (visible)
					{
						setTimeout(BX.delegate(function(visible){
							BX.MessengerCommon.readMessage(dialogId, true, true);
						}, this), 300)
					}
				},this));
			}
			if (!showNewMessage)
			{
				continue;
			}

			var isLines = i.toString().substr(0,4) == 'chat' && this.BXIM.messenger.chat[i.toString().substr(4)] && this.BXIM.messenger.chat[i.toString().substr(4)].type == 'lines';

			if (typeof (this.BXIM.messenger.flashMessage[i]) == 'undefined')
			{
				this.BXIM.messenger.flashMessage[i] = {};
			}

			for (var k = 0; k < unreadMessage[i].length; k++)
			{
				if (this.BXIM.messenger.message[unreadMessage[i][k]]?.params?.NOTIFY === 'N')
				{
					this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
					continue;
				}

				if (isLines && BX.MessengerCommon.getCounter(i) > 0)
				{
					var senderId = this.BXIM.messenger.message[unreadMessage[i][k]].senderId;
					if (senderId == 0 || this.BXIM.messenger.users[senderId].extranet)
					{
						this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
						continue;
					}
				}

				var resultOfNameSearch = this.BXIM.messenger.message[unreadMessage[i][k]].text.match(new RegExp("("+this.BXIM.messenger.users[this.BXIM.userId].name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'));
				if (
					!resultOfNameSearch
					&& (
						userStatus == 'dnd'
						|| this.BXIM.notify.muteModeCode > 0
						|| BX.MessengerCalls && BX.MessengerCalls.hasActiveSharing()
					)
				)
				{

					this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
				}
				else
				{
					this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = true;
				}
			}
		}

		this.BXIM.messenger.dialogStatusRedraw(this.isMobile()? {type: 1, slidingPanelRedrawDisable: true, 'userRedraw': false}: {'userRedraw': false});

		this.BXIM.messenger.newMessage(true);
		this.BXIM.messenger.updateMessageCount(true);
	}

	MessengerCommon.prototype.redrawDateMarks = function()
	{
		if (!this.BXIM.messenger.popupMessengerBodyWrap)
			return false;

		if (typeof(this.BXIM.messenger.popupMessengerBodyWrap.getElementsByClassName) == 'undefined')
			return false;

		var element = {};
		var contentGroup = this.BXIM.messenger.popupMessengerBodyWrap.getElementsByClassName("bx-messenger-content-group");
		var marginTop = this.BXIM.messenger.popupMessengerBody.getBoundingClientRect().top;
		for (var i = 0; i < contentGroup.length; i++)
		{
			element = BX.MessengerCommon.isElementCoordsBelow(contentGroup[i], this.BXIM.messenger.popupMessengerBody, 33, true);
			if (contentGroup[i].className != "bx-messenger-content-group bx-messenger-content-group-today")
			{
				contentGroup[i].className = "bx-messenger-content-group "+(element.top? "": "bx-messenger-content-group-float");
				contentGroup[i].firstChild.nextSibling.style.marginLeft = element.top? "": Math.round(contentGroup[i].offsetWidth/2 - contentGroup[i].firstChild.nextSibling.offsetWidth/2)+'px';
				contentGroup[i].firstChild.nextSibling.style.marginTop = element.top? "": ((-element.coords.top)+14)+'px';
			}
			if (!element.top && contentGroup[i-1])
			{
				contentGroup[i-1].className = "bx-messenger-content-group";
				contentGroup[i-1].firstChild.nextSibling.style.marginLeft = '';
				contentGroup[i-1].firstChild.nextSibling.style.marginTop = '';
			}
		}
	}

	MessengerCommon.prototype.unreadMessage = function(messageId) // TODO unreadMessage
	{
		if (!this.BXIM.messenger.message[messageId])
		{
			return false;
		}

		var message = this.BXIM.messenger.message[messageId];

		var dialogId = '';
		if (message.recipientId.toString().substr(0,4) == 'chat')
		{
			dialogId = message.recipientId;
		}
		else
		{
			dialogId = message.senderId;
		}

		BX.rest.callMethod('im.dialog.unread', {
			DIALOG_ID: dialogId,
			MESSAGE_ID: messageId
		});

		showMessage = this.BXIM.messenger.showMessage[dialogId];
		showMessage.sort(function(i, ii) {if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}});

		this.BXIM.messenger.unreadMessage[dialogId] = [];

		var counter = 0;
		for (var i = 0; i < showMessage.length; i++)
		{
			if (parseInt(showMessage[i]) >= parseInt(messageId))
			{
				if (!this.BXIM.messenger.unreadMessage[dialogId])
					this.BXIM.messenger.unreadMessage[dialogId] = [];

				this.BXIM.messenger.unreadMessage[dialogId].push(showMessage[i]);
				counter++;
			}
		}

		this.recentListUpdateItem({
			id: dialogId,
			counter: counter,
		});

		this.skipReadMessage = true;

		this.drawTab();
		this.recentListRedraw();

		setTimeout(BX.delegate(function(){
			this.skipReadMessage = false;
		},this), 1000);
	}

	MessengerCommon.prototype.readMessage = function(dialogId, send, sendAjax, skipCheck)
	{
		if (!dialogId || this.skipReadMessage)
		{
			return false;
		}

		send = send != false;
		sendAjax = sendAjax !== false;

		if (sendAjax)
		{
			skipCheck = skipCheck == true || this.isMobile();
			if (!skipCheck && !BX.MessengerCommon.getCounter(dialogId))
			{
				return false;
			}

			if (dialogId.toString().substring(0, 4) == 'chat')
			{
				var chatId = dialogId.toString().substring(4);
				if (
					this.BXIM.messenger.chat[chatId]
					&& this.BXIM.messenger.chat[chatId].type == 'lines'
					&& this.BXIM.messenger.chat[chatId].owner == 0
				)
				{
					return false;
				}
			}

			if (
				BX.SidePanel
				&& BX.SidePanel.Instance.isOpen()
				&& BX.SidePanel.Instance.isOnTop()
				&& this.BXIM.messenger.popupMessenger
			)
			{
				var topSlider = BX.SidePanel.Instance.getTopSlider();
				if (!(topSlider.url === '/desktop_app/' || topSlider.url.startsWith('im:slider')))
				{
					return false;
				}
			}

		}

		var oldCounter = BX.MessengerCommon.getCounter(dialogId);
		this.recentListUpdateItem({
			id: dialogId,
			counter: 0,
			unread: false
		});
		this.recentListRedraw();

		var lastId = 0;
		if (Math && this.BXIM.messenger.unreadMessage[dialogId])
			lastId = Math.max.apply(Math, this.BXIM.messenger.unreadMessage[dialogId]);

		if (this.BXIM.messenger.unreadMessage[dialogId])
		{
			var unreadedMessageUserBackup = BX.clone(this.BXIM.messenger.unreadMessage[dialogId]);
			delete this.BXIM.messenger.unreadMessage[dialogId];
		}

		if (this.BXIM.messenger.flashMessage[dialogId])
			delete this.BXIM.messenger.flashMessage[dialogId];

		if (!this.isMobile())
		{
			this.BXIM.messenger.updateMessageCount(send);
			this.BXIM.updateCounter();
		}

		if (
			this.BXIM.messenger.popupMessenger != null
			&& dialogId == this.BXIM.messenger.currentTab
		)
		{
			elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-new", false);
			if (elements != null)
			{
				for (var i = 0; i < elements.length; i++)
				{
					if (elements[i].getAttribute('data-notifyType') != 1)
					{
						BX.removeClass(elements[i], 'bx-messenger-content-item-new');
					}
				}
			}
		}

		if (sendAjax)
		{
			if (BX.MessengerProxy)
			{
				BX.MessengerProxy.sendCounterChangeEvent(dialogId, 0);
			}
			var sendData = {'IM_READ_MESSAGE' : 'Y', 'USER_ID' : dialogId, 'TAB' : this.BXIM.messenger.currentTab, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
			if (parseInt(lastId) > 0)
				sendData['LAST_ID'] = lastId;
			var _ajax = BX.ajax({
				url: this.BXIM.pathToAjax+'?READ_MESSAGE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				skipAuthCheck: true,
				data: sendData,
				onsuccess: BX.delegate(function(data)
				{
					if (data)
					{
						if(data.BITRIX_SESSID)
							BX.message({'bitrix_sessid': data.BITRIX_SESSID});

						if (data.ERROR == '')
						{
							BX.onCustomEvent(window, 'onImMessageRead', [dialogId]);
							this.BXIM.messenger.setUpdateStateStep();
						}
						else
						{
							this.BXIM.messenger.unreadMessage[dialogId] = unreadedMessageUserBackup;
							if (BX.MessengerProxy)
							{
								BX.MessengerProxy.sendCounterChangeEvent(dialogId, oldCounter);
							}
							if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
							{
								this.BXIM.messenger.sendAjaxTry++;
								setTimeout(BX.delegate(function(){
									this.readMessage(dialogId, false, true);
								}, this), 2000);
								BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
							}
							else if (data.ERROR == 'AUTHORIZE_ERROR')
							{
								this.BXIM.messenger.sendAjaxTry++;
								if (this.isDesktop() || this.isMobile())
								{
									setTimeout(BX.delegate(function(){
										this.readMessage(dialogId, false, true);
									}, this), 10000);
								}
								BX.onCustomEvent(window, 'onImError', [data.ERROR]);
							}
						}
					}
					else
					{
						if (BX.MessengerProxy)
						{
							BX.MessengerProxy.sendCounterChangeEvent(dialogId, oldCounter);
						}
						this.BXIM.messenger.unreadMessage[dialogId] = unreadedMessageUserBackup;
					}
				}, this),
				onfailure: BX.delegate(function()
				{
					if (BX.MessengerProxy)
					{
						BX.MessengerProxy.sendCounterChangeEvent(dialogId, oldCounter);
					}
					this.BXIM.messenger.unreadMessage[dialogId] = unreadedMessageUserBackup;

					this.BXIM.messenger.sendAjaxTry = 0;
					try {
						if (typeof(_ajax) == 'object' && _ajax.status == 0)
							BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
					}
					catch(e) {}
				}, this)
			});
		}
		if (send)
		{
			BX.localStorage.set('mrm', dialogId, 5);
			BX.localStorage.set('mnnb', true, 1);
		}
	};

	MessengerCommon.prototype.drawReadMessageChat = function(chatId, animation)
	{
		if (!this.BXIM.messenger.readedList[chatId])
		{
			return false;
		}

		var lastId = Math.max.apply(Math, this.BXIM.messenger.showMessage[chatId]);

		var readedCount = 0;
		var newReadedList = {};
		var firstUserId = 0;
		var firstUserDate = false;
		for (var userId in this.BXIM.messenger.readedList[chatId])
		{
			if (userId == this.BXIM.userId)
				continue;

			if (this.BXIM.messenger.message[lastId] && this.BXIM.messenger.message[lastId].senderId == userId)
				continue;

			if (this.BXIM.messenger.readedList[chatId][userId].messageId >= lastId)
			{
				if (!newReadedList[userId])
				{
					newReadedList[userId] = {};
				}
				if (!firstUserDate || firstUserDate.getTime() > this.BXIM.messenger.readedList[chatId][userId].date.getTime())
				{
					firstUserId = userId;
					firstUserDate = this.BXIM.messenger.readedList[chatId][userId].date;
				}
				newReadedList[userId] = this.BXIM.messenger.readedList[chatId][userId];
				readedCount++;
			}
		}
		if (readedCount > 0)
		{
			this.BXIM.messenger.readedList[chatId] = newReadedList;
		}
		else
		{
			this.BXIM.messenger.readedList[chatId] = false;
			var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap? this.BXIM.messenger.popupMessengerBodyWrap.lastChild: null;
			if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify"))
			{
				if (!this.countWriting(chatId))
				{
					BX.remove(lastMessage);
				}
			}

			return false;
		}

		if (!this.countWriting(chatId))
		{
			var userData = this.getUserParam(firstUserId);
			var usersText = '<span title="'+this.formatDate(firstUserDate)+'">'+userData.name+'</span>';
			if (readedCount > 1)
			{
				if (this.isMobile())
				{
					usersText = BX.message('IM_M_READED_CHAT_MORE')
						.replace('#USER#', usersText)
						.replace('#LINK_START#', '<b>')
						.replace('#LINK_END#', '</b>')
						.replace('#COUNT#', (readedCount-1));
				}
				else
				{
					usersText = BX.message('IM_M_READED_CHAT_MORE')
						.replace('#USER#', usersText)
						.replace('#LINK_START#', '<span class="bx-messenger-ajax" data-entity="readedList">')
						.replace('#LINK_END#', '</span>')
						.replace('#COUNT#', (readedCount-1));
				}
			}

			animation = animation != false;
			this.drawNotifyMessage(chatId, 'readed', BX.message('IM_M_READED_CHAT').replace('#USERS#', usersText), animation);
		}
	};

	MessengerCommon.prototype.drawReadMessage = function(userId, messageId, date, animation)
	{
		var lastId = Math.max.apply(Math, this.BXIM.messenger.showMessage[userId]);
		if (lastId != messageId || this.BXIM.messenger.message[lastId].senderId == userId || !date)
		{
			this.BXIM.messenger.readedList[userId] = false;
			return false;
		}

		this.BXIM.messenger.readedList[userId] = {
			'messageId' : messageId,
			'date' : date
		};
		if (!this.countWriting(userId))
		{
			animation = animation != false;

			this.drawNotifyMessage(userId, 'readed', BX.message('IM_M_READED').replace('#DATE#', this.formatDate(date)), animation);
		}
	};

	MessengerCommon.prototype.drawNotifyMessage = function(userId, icon, message, animation)
	{
		if (this.BXIM.messenger.popupMessenger == null || userId != this.BXIM.messenger.currentTab || typeof(message) == 'undefined' || typeof(icon) == 'undefined' || userId == 0)
			return false;

		if (!this.BXIM.messenger.popupMessengerBodyWrap)
			return false;

		var lastChild = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
		if (!lastChild || BX.hasClass(lastChild, "bx-messenger-content-empty"))
			return false;

		var arMessage = BX.create("div", { attrs : { 'data-type': 'notify'}, props: { className : "bx-messenger-content-item bx-messenger-content-item-notify"}, children: [
			BX.create("span", { props : { className : "bx-messenger-content-item-content"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
					BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, html: '<span class="bx-messenger-content-item-notify-icon-'+icon+'"></span>'+this.prepareText(message, false, true, true)})
				]})
			]})
		]});

		var enableScroll = true;
		if (BX.hasClass(lastChild, "bx-messenger-content-item-notify"))
		{
			enableScroll = false;
			BX.remove(lastChild);
		}
		this.BXIM.messenger.popupMessengerBodyWrap.appendChild(arMessage);

		animation = animation != false;
		if (enableScroll && this.BXIM.messenger.popupMessengerBody && BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight))
		{
			if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
			}
			else if (this.BXIM.animationSupport && animation)
			{
				if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
					this.BXIM.messenger.popupMessengerBodyAnimation.stop();
				(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
					duration : 1200,
					start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
					finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
					step : BX.delegate(function(state){
						this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
					}, this)
				})).animate();
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}
	};

	MessengerCommon.prototype.loadHistory = function(userId, isHistoryDialog, loadFromButton)
	{
		isHistoryDialog = typeof(isHistoryDialog) == 'undefined'? true: isHistoryDialog;
		loadFromButton = typeof(loadFromButton) == 'undefined'? false: loadFromButton;

		if (!this.BXIM.messenger.historyEndOfList[userId])
			this.BXIM.messenger.historyEndOfList[userId] = {};

		if (!this.BXIM.messenger.historyLoadFlag[userId])
			this.BXIM.messenger.historyLoadFlag[userId] = {};

		if (this.BXIM.messenger.historyLoadFlag[userId] && this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog])
		{
			if (this.isMobile())
				app.pullDownLoadingStop();
			return;
		}

		if (this.isMobile())
		{
			isHistoryDialog = false;
		}
		else
		{
			if (isHistoryDialog)
			{
				if (this.BXIM.messenger.historySearch != "" || this.BXIM.messenger.historyDateSearch != "")
					return;

				if (!(this.BXIM.messenger.popupHistoryItems.scrollTop > this.BXIM.messenger.popupHistoryItems.scrollHeight - this.BXIM.messenger.popupHistoryItems.offsetHeight - 100))
					return;
			}
			else
			{
				if (this.BXIM.messenger.showMessage[userId] && this.BXIM.messenger.showMessage[userId].length > 0 && this.BXIM.messenger.popupMessengerBody.scrollTop >= 5)
					return;
			}
		}

		if (!this.BXIM.messenger.historyEndOfList[userId] || !this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog])
		{
			var elements = [];
			if (isHistoryDialog)
			{
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupHistoryBodyWrap, "bx-messenger-history-item");
			}
			else
			{
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-text-wrap");
			}

			if (!this.isMobile() && elements.length < 30 && !loadFromButton)
			{
				return false;
			}

			if (elements.length > 0)
				this.BXIM.messenger.historyOpenPage[userId] = Math.floor(elements.length/30)+1;
			else
				this.BXIM.messenger.historyOpenPage[userId] = 1;

			var tmpLoadMoreWait = null;
			if (!this.isMobile() && !loadFromButton)
			{
				tmpLoadMoreWait = BX.create("div", { props : { className : "bx-messenger-content-load-more-history" }, children : [
					BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
					BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
				]});
				if (isHistoryDialog)
				{
					this.BXIM.messenger.popupHistoryBodyWrap.appendChild(tmpLoadMoreWait);
				}
				else
				{
					this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(tmpLoadMoreWait, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);
				}
			}
			else if (loadFromButton)
			{
				tmpLoadMoreWait = BX.create("div", { props : { className : "bx-messenger-content-load-more-history" }, children : [
					BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
					BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
				]});
				var buttonElement = BX.findChildByClassName(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-content-empty');
				if (buttonElement)
				{
					buttonElement.innerHTML = '';
					buttonElement.appendChild(tmpLoadMoreWait);
				}
				else
				{
					buttonElement = BX.findChildByClassName(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-notifier-content-link-history-empty');
					this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(tmpLoadMoreWait, buttonElement);
					BX.remove(buttonElement);
				}
			}

			if (!this.BXIM.messenger.historyLoadFlag[userId])
				this.BXIM.messenger.historyLoadFlag[userId] = {};

			this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog] = true;

			BX.ajax({
				url: this.BXIM.pathToAjax+'?HISTORY_LOAD_MORE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				timeout: 30,
				data: {'IM_HISTORY_LOAD_MORE' : 'Y', 'USER_ID' : userId, 'PAGE_ID' : this.BXIM.messenger.historyOpenPage[userId], 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);

					if (this.isMobile())
						app.pullDownLoadingStop();

					this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog] = false;

					if (data.MESSAGE && data.MESSAGE.length == 0)
					{
						this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog] = true;
						var lastMessageElementDate = BX.findChildByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-empty");
						if (lastMessageElementDate)
						{
							lastMessageElementDate.appendChild(
								BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_NO_MESSAGE')})
							);
						}
						return;
					}

					for (var i in data.FILES)
					{
						if (!this.BXIM.disk.files[data.CHAT_ID])
							this.BXIM.disk.files[data.CHAT_ID] = {};
						if (this.BXIM.disk.files[data.CHAT_ID][i])
							continue;
						data.FILES[i].date = new Date(data.FILES[i].date);
						this.BXIM.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					var countMessages = 0;
					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						data.MESSAGE[i].textOriginal = data.MESSAGE[i].text;
						data.MESSAGE[i].text = BX.MessengerCommon.prepareText(data.MESSAGE[i].text, true, true, true);

						this.BXIM.messenger.message[i] = data.MESSAGE[i];

						countMessages++;
					}
					if (countMessages < 30)
					{
						this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog] = true;
					}

					for (var i in data.USERS_MESSAGE)
					{
						if (isHistoryDialog)
						{
							if (this.BXIM.messenger.history[i])
								this.BXIM.messenger.history[i] = BX.util.array_merge(this.BXIM.messenger.history[i], data.USERS_MESSAGE[i]);
							else
								this.BXIM.messenger.history[i] = data.USERS_MESSAGE[i];
						}
						else
						{
							if (this.BXIM.messenger.showMessage[i])
								this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
							else
								this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
						}
					}

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}

					for (var i in data.PHONES)
					{
						this.BXIM.messenger.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}

					if (isHistoryDialog)
					{
						for (var i = 0; i < data.USERS_MESSAGE[userId].length; i++)
						{
							var history = this.BXIM.messenger.message[data.USERS_MESSAGE[userId][i]];
							if (history)
							{
								if (BX('im-message-history-'+history.id))
									continue;

								var dateGroupTitle = BX.MessengerCommon.formatDate(history.date, BX.MessengerCommon.getDateFormatType('MESSAGE_TITLE'));
								var dataGroupCode = typeof(BX.translit) != 'undefined'? BX.translit(dateGroupTitle): dateGroupTitle;
								if (!BX('bx-im-history-'+dataGroupCode))
								{
									var dateGroupTitleNode = BX.create("div", {props : { className: "bx-messenger-content-group bx-messenger-content-group-history"}, children : [
										BX.create("div", {attrs: {id: 'bx-im-history-'+dataGroupCode}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
									]});
									this.BXIM.messenger.popupHistoryBodyWrap.appendChild(dateGroupTitleNode);
								}

								var history = this.BXIM.messenger.drawMessageHistory(history);
								if (history)
									this.BXIM.messenger.popupHistoryBodyWrap.appendChild(history);
							}
						}
					}
					else
					{
						var lastChildBeforeChangeDom = this.BXIM.messenger.popupMessengerBodyWrap.firstChild? this.BXIM.messenger.popupMessengerBodyWrap.firstChild.nextSibling: null;
						if (lastChildBeforeChangeDom)
						{
							lastChildBeforeChangeDom = BX('im-message-'+lastChildBeforeChangeDom.getAttribute('data-blockmessageid'));
						}

						if (data.USERS_MESSAGE[userId])
						{
							for (var i = 0; i < data.USERS_MESSAGE[userId].length; i++)
							{
								var history = this.BXIM.messenger.message[data.USERS_MESSAGE[userId][i]];
								if (history)
								{
									if (BX('im-message-'+history.id))
										continue;

									BX.MessengerCommon.drawMessage(userId, history, false, true);
								}
							}
						}
						if (lastChildBeforeChangeDom)
						{
							this.scrollToNode(lastChildBeforeChangeDom.parentNode.parentNode.parentNode.parentNode.parentNode);
						}
						else
						{
							this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
						}
					}
				}, this),
				onfailure: BX.delegate(function(){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					if (this.isMobile())
						app.pullDownLoadingStop();
				},this)
			});
		}
	};

	MessengerCommon.prototype.loadMessageByDate = function(chatId, lastLoadDate, firstMessageId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?LOAD_MESSAGE_BY_DATE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_LOAD_MESSAGE_BY_DATE' : 'Y', 'CHAT_ID' : chatId, 'LAST_LOAD' : lastLoadDate, 'FIRST_MESSAGE_ID' : firstMessageId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					var dialogId = data.DIALOG_ID;

					this.BXIM.messenger.sendAjaxTry = 0;

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}

					for (var i in data.PHONES)
					{
						this.BXIM.messenger.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}


					for (var i in data.FILES)
					{
						if (!this.BXIM.messenger.disk.files[data.CHAT_ID])
							this.BXIM.messenger.disk.files[data.CHAT_ID] = {};

						data.FILES[i].date = new Date(data.FILES[i].date);
						this.BXIM.messenger.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					this.BXIM.messenger.sendAjaxTry = 0;
					var messageCnt = 0;
					for (var i in data.MESSAGE)
					{
						messageCnt++;

						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						data.MESSAGE[i].textOriginal = data.MESSAGE[i].text;
						data.MESSAGE[i].text = BX.MessengerCommon.prepareText(data.MESSAGE[i].text, true, true, true);

						this.BXIM.messenger.message[i] = data.MESSAGE[i];
						this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
					}

					for (var i in data.USERS_MESSAGE)
					{
						if (this.BXIM.messenger.showMessage[i])
							this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
						else
							this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
					}

					for (var i in data.DELETE_MESSAGE)
					{
						delete this.BXIM.messenger.message[i];
						if (this.BXIM.messenger.currentTab == data.DIALOG_ID && BX('im-message-'+i))
						{
							var messageWrap = BX('im-message-'+i).parentNode.parentNode.parentNode.parentNode.parentNode;
							if (messageWrap.getAttribute('data-messageId') == messageWrap.getAttribute('data-blockMessageId'))
							{
								BX.remove(messageWrap);
							}
							else
							{
								messageWrap = BX('im-message-'+i).parentNode;
								if (messageWrap.nextSibling && BX.hasClass(messageWrap.nextSibling, 'bx-messenger-hr'))
								{
									BX.remove(messageWrap.nextSibling);
								}
								else if (!messageWrap.nextSibling && BX.hasClass(messageWrap.previousSibling, 'bx-messenger-hr'))
								{
									BX.remove(messageWrap.previousSibling);
								}
								BX.remove(messageWrap);
							}
						}
					}

					for (var i in data.CHAT)
					{
						data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
						this.BXIM.messenger.chat[i] = data.CHAT[i];
					}
					for (var i in data.USER_IN_CHAT)
					{
						this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
					}
					for (var i in data.USER_BLOCK_CHAT)
					{
						this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
					}

					this.changeUnreadMessage(data.UNREAD_MESSAGE);
				}
				else
				{
					if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry < 2)
					{
						this.sendAjaxTry++;
						setTimeout(BX.delegate(function(){this.loadMessageByDate(chatId, lastLoadDate, firstMessageId)}, this), 1000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.sendAjaxTry++;
						if (BX.MessengerCommon.isDesktop() || this.isMobile())
						{
							setTimeout(BX.delegate(function (){
								this.loadMessageByDate(chatId, lastLoadDate, firstMessageId)
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

	MessengerCommon.prototype.loadUserData = function(userId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?USER_DATA_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_USER_DATA_LOAD' : 'Y', 'USER_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR == '')
				{
					this.BXIM.messenger.userChat[userId] = data.CHAT_ID;

					BX.MessengerCommon.getUserParam(userId, true);
					this.BXIM.messenger.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}
					for (var i in data.PHONES)
					{
						this.BXIM.messenger.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}

					if (this.isMobile())
					{
						this.BXIM.messenger.dialogStatusRedrawDelay();
					}
					else
					{
						this.BXIM.messenger.dialogStatusRedraw();
					}
				}
				else
				{
					this.BXIM.messenger.redrawTab[userId] = true;
					if (data.ERROR == 'ACCESS_DENIED' && this.BXIM.messenger.currentTab == userId)
					{
						this.BXIM.messenger.currentTab = 0;
						this.BXIM.messenger.openChatFlag = false;
						this.BXIM.messenger.openCallFlag = false;
						this.BXIM.messenger.openLinesFlag = false;
						this.BXIM.messenger.extraClose();
					}
				}
			}, this)
		});
	};

	MessengerCommon.prototype.loadChatData = function(chatId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_DATA_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_CHAT_DATA_LOAD' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR == '')
				{
					if (this.BXIM.messenger.chat[data.CHAT_ID].fake)
					{
						this.BXIM.messenger.chat[data.CHAT_ID].name = BX.message('IM_M_USER_NO_ACCESS');
					}

					for (var i in data.CHAT)
					{
						data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
						this.BXIM.messenger.chat[i] = data.CHAT[i];
					}
					for (var i in data.USER_IN_CHAT)
					{
						this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
					}
					for (var i in data.USER_BLOCK_CHAT)
					{
						this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
					}
					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}

					if (this.BXIM.messenger.currentTab == 'chat'+data.CHAT_ID)
					{
						if (this.BXIM.messenger.chat[data.CHAT_ID] && this.BXIM.messenger.chat[data.CHAT_ID].type == 'call')
						{
							this.BXIM.messenger.openCallFlag = true;
						}
						else if (this.BXIM.messenger.chat[data.CHAT_ID] && this.BXIM.messenger.chat[data.CHAT_ID].type == 'lines')
						{
							this.BXIM.messenger.openLinesFlag = true;
						}
						this.drawTab(this.BXIM.messenger.currentTab);
					}
				}
			}, this)
		});
	};

	MessengerCommon.prototype.loadLastMessage = function(userId, callback)
	{
		if (this.BXIM.messenger.loadLastMessageTimeout[userId])
			return false;

		callback = typeof(callback) == 'function'? callback: function(userId, result, data){};

		var chatId = 0;
		var userIsChat = false;
		if (userId.toString().substr(0,4) == 'chat')
		{
			chatId = userId.toString().substr(4);
			userIsChat = true;
		}
		else if (userId.toString().substr(0,2) == 'sg')
		{
			chatId = userId.toString().substr(2);
			userIsChat = true;
		}
		else if (userId.toString().substr(0,3) == 'crm')
		{
			chatId = userId.toString().substr(4);
			userIsChat = true;
		}

		this.BXIM.messenger.historyWindowBlock = true;
		this.BXIM.messenger.loadLastMessageTimeout[userId] = true;

		if (this.BXIM.messenger.popupMessengerDialog && this.BXIM.messenger.currentTab == userId)
		{
			if (
				(userIsChat && (!this.BXIM.messenger.chat[chatId] || this.BXIM.messenger.chat[chatId].fake))
				|| (!userIsChat && (!this.BXIM.messenger.users[userId] || this.BXIM.messenger.users[userId].fake))
			)
			{
				BX.addClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message");
			}
		}

		var onfailure = BX.delegate(function(){
			this.BXIM.messenger.loadLastMessageTimeout[userId] = false;

			callback(userId, false, {});

			if (this.BXIM.messenger.popupMessengerDialog && this.BXIM.messenger.currentTab == userId)
			{
				BX.removeClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message");
			}
			if (this.BXIM.messenger.sendAjaxTry < 2)
			{
				this.BXIM.messenger.sendAjaxTry++;
				clearTimeout(this.BXIM.messenger.loadLastMessageTimeout);
				this.BXIM.messenger.loadLastMessageTimeout = setTimeout(BX.delegate(function(){
					BX.MessengerCommon.loadLastMessage(userId);
				}, this), 2000);

				return true;
			}

			this.BXIM.messenger.historyWindowBlock = false;
			this.BXIM.messenger.redrawTab[userId] = true;

			if (!this.BXIM.messenger.showMessage[userId] || this.BXIM.messenger.showMessage[userId].length <= 0)
			{
				this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';

				var arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
					BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_ERROR")})
				]})];

				BX.adjust(this.BXIM.messenger.popupMessengerBodyWrap, {children: arMessage});

				if (this.isMobile() && this.MobileActionEqual('DIALOG'))
				{
					BXMobileApp.UI.Page.TopBar.title.setText(BX.message('IM_F_ERROR'));
					BXMobileApp.UI.Page.TopBar.title.setDetailText('');
				}
			}
			else
			{
				this.BXIM.messenger.tooltip(this.BXIM.messenger.popupMessengerBody, BX.message("IM_M_LOAD_ERROR"), {offsetTop: -10, offsetLeft: 50, bindOptions: {position: "top"}});

				var loadHistoryBlock = BX.findChildByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-notifier-content-link-history");
				if (loadHistoryBlock)
				{
					BX.remove(loadHistoryBlock);
				}

			}

		}, this);

		var onsuccess = BX.delegate(function(data)
		{
			this.BXIM.messenger.loadLastMessageTimeout[userId] = false;

			if (!data)
			{
				onfailure();
				return false;
			}

			if (this.BXIM.messenger.popupMessengerDialog && this.BXIM.messenger.currentTab == data.USER_ID)
			{
				BX.removeClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message");
			}

			this.BXIM.checkRevision(this.isMobile()? data.MOBILE_REVISION: data.REVISION);

			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}

			if (data.ERROR == '')
			{
				if (this.isMobile())
				{
					this.BXIM.disk.setChatParams(parseInt(data.CHAT_ID), parseInt(data.DISK_FOLDER_ID));
				}

				if (userIsChat)
				{
					if (data.USER_ID.toString().substr(0,2) == 'sg')
					{
						if (this.BXIM.messenger.currentTab == data.USER_ID)
						{
							this.BXIM.messenger.currentTab = 'chat'+data.CHAT_ID;
						}
						delete this.BXIM.messenger.chat[data.USER_ID];

						data.USER_ID = 'chat'+data.CHAT_ID;
						BX.MessengerCommon.getUserParam(data.USER_ID);
					}
					else if (data.USER_ID.toString().substr(0,3) == 'crm')
					{
						if (this.BXIM.messenger.currentTab == data.USER_ID)
						{
							this.BXIM.messenger.currentTab = 'chat'+data.CHAT_ID;
						}
						delete this.BXIM.messenger.chat[data.USER_ID];

						data.USER_ID = 'chat'+data.CHAT_ID;
						BX.MessengerCommon.getUserParam(data.USER_ID);
					}
				}
				else
				{
					this.BXIM.messenger.userChat[userId] = data.CHAT_ID;

					BX.MessengerCommon.getUserParam(userId, true);
					this.BXIM.messenger.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');
				}

				for (var i in data.USERS)
				{
					data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
					data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
					data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
					data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

					this.BXIM.messenger.users[i] = data.USERS[i];
				}

				for (var i in data.PHONES)
				{
					this.BXIM.messenger.phones[i] = {};
					for (var j in data.PHONES[i])
					{
						this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
					}
				}
				for (var i in data.USER_IN_GROUP)
				{
					if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined' || typeof(this.BXIM.messenger.userInGroup[i].users) == 'undefined' || !this.BXIM.messenger.userInGroup[i].users.length)
					{
						this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
					}
					else
					{
						for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
							this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

						this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
					}
				}

				if (!userIsChat && data.USER_LOAD == 'Y')
					BX.MessengerCommon.userListRedraw();

				for (var i in data.FILES)
				{
					if (!this.BXIM.messenger.disk.files[data.CHAT_ID])
						this.BXIM.messenger.disk.files[data.CHAT_ID] = {};

					data.FILES[i].date = new Date(data.FILES[i].date);
					this.BXIM.messenger.disk.files[data.CHAT_ID][i] = data.FILES[i];
				}

				this.BXIM.messenger.sendAjaxTry = 0;
				var messageCnt = 0;
				for (var i in data.MESSAGE)
				{
					messageCnt++;

					data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
					data.MESSAGE[i].textOriginal = data.MESSAGE[i].text;
					data.MESSAGE[i].text = BX.MessengerCommon.prepareText(data.MESSAGE[i].text, true, true, true);

					this.BXIM.messenger.message[i] = data.MESSAGE[i];
					this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
				}

				if (messageCnt > 0)
				{
					delete this.BXIM.messenger.redrawTab[userId];
				}

				if (typeof this.BXIM.messenger.showMessage[userId] !== 'undefined')
				{
					this.BXIM.messenger.showMessage[userId] = this.BXIM.messenger.showMessage[userId].filter(function(element) {
						return element.toString().startsWith('birthday') || element.toString().startsWith('temp');
					});
				}

				for (var i in data.USERS_MESSAGE)
				{
					if (this.BXIM.messenger.showMessage[i])
						this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
					else
						this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
				}
				if (userIsChat && this.BXIM.messenger.chat[data.USER_ID.toString().substr(4)] && this.BXIM.messenger.chat[data.USER_ID.toString().substr(4)].fake)
				{
					this.BXIM.messenger.chat[data.USER_ID.toString().substr(4)].name = BX.message('IM_M_USER_NO_ACCESS');
				}

				for (var i in data.CHAT)
				{
					data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
					this.BXIM.messenger.chat[i] = data.CHAT[i];
				}
				for (var i in data.USER_IN_CHAT)
				{
					this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
				}
				for (var i in data.USER_BLOCK_CHAT)
				{
					this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
				}

				if (this.isMobile() && typeof fabric != 'undefined')
				{
					fabric.Answers.sendCustomEvent("imOpenDialog", {});

					if (data.CHAT && data.CHAT[data.CHAT_ID])
					{
						if (data.CHAT[data.CHAT_ID].type == 'lines')
							fabric.Answers.sendCustomEvent("imOpenDialogLines", {});
						else
							fabric.Answers.sendCustomEvent("imOpenDialogChat", {});
					}
					else
					{
						fabric.Answers.sendCustomEvent("imOpenDialogPrivate", {});
					}
				}

				if (data.OPENLINES.canVoteAsHead)
				{
					if (!this.BXIM.messenger.openlines.canVoteAsHead)
					{
						this.BXIM.messenger.openlines.canVoteAsHead = {};
					}

					for (var i in data.OPENLINES.canVoteAsHead)
					{
						this.BXIM.messenger.openlines.canVoteAsHead[i] = data.OPENLINES.canVoteAsHead[i];
					}
				}

				if (this.BXIM.messenger.currentTab == data.USER_ID)
				{
					if (this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat' && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == 'call')
					{
						this.BXIM.messenger.openCallFlag = true;
					}
				}
				if (data.NETWORK_ID != '')
				{
					this.BXIM.messenger.currentTab = data.USER_ID? data.USER_ID: 0;

					delete this.BXIM.messenger.users[data.NETWORK_ID];
					if (!this.BXIM.messenger.bot[data.USER_ID])
					{
						this.BXIM.messenger.bot[data.USER_ID] = this.BXIM.messenger.bot[data.NETWORK_ID];
					}
					delete this.BXIM.messenger.bot[data.NETWORK_ID];

					if (this.isMobile() && this.MobileActionEqual('DIALOG'))
					{
						app.onCustomEvent('onImDialogNetworkOpen', {NETWORK_ID: data.NETWORK_ID, USER_ID: data.USER_ID, USER: this.BXIM.messenger.users[data.USER_ID]});
					}
				}

				if (userIsChat)
				{
					for (var i in data.READED_LIST)
					{
						for (var ii in data.READED_LIST[i])
						{
							data.READED_LIST[i][ii].date = new Date(data.READED_LIST[i][ii].date);
						}
						this.BXIM.messenger.readedList[i] = data.READED_LIST[i];
					}
				}
				else
				{
					for (var i in data.READED_LIST)
					{
						data.READED_LIST[i].date = new Date(data.READED_LIST[i].date);
						this.BXIM.messenger.readedList[i] = data.READED_LIST[i];
					}
				}

				if (userIsChat && this.BXIM.messenger.chat[data.CHAT_ID] && this.BXIM.messenger.chat[data.CHAT_ID].type == 'livechat')
				{
					var session = this.livechatGetSession(data.CHAT_ID);
					if (session.readed == 'Y')
					{
						session.readedTime = session.readedTime? new Date(session.readedTime): new Date();
						this.BXIM.messenger.readedList['chat'+data.CHAT_ID] = {
							'messageId' : session.readedId,
							'date' : session.readedTime
						};
					}
				}

				this.changeUnreadMessage(data.UNREAD_MESSAGE);

				this.drawTab(data.USER_ID, this.BXIM.messenger.currentTab == data.USER_ID, messageCnt);

				if (this.BXIM.messenger.currentTab == data.USER_ID && this.BXIM.messenger.readedList[data.USER_ID])
				{
					if (this.BXIM.messenger.openChatFlag)
					{
						this.drawReadMessageChat(data.USER_ID, false);
					}
					else
					{
						this.drawReadMessage(data.USER_ID, this.BXIM.messenger.readedList[data.USER_ID].messageId, this.BXIM.messenger.readedList[data.USER_ID].date, false);
					}
				}

				this.BXIM.messenger.historyWindowBlock = false;

				if (this.BXIM.isFocus())
				{
					this.readMessage(data.USER_ID, true, BX.MessengerCommon.getCounter(data.USER_ID) > 0);
				}

				if (this.isMobile())
				{
					setTimeout(BX.delegate(function(){this.BXIM.messenger.autoScroll()}, this), 100);
				}

				BX.onCustomEvent(window, 'onImLoadLastMessage', [userId, true, data]);
				callback(userId, true, data);
			}
			else
			{
				this.BXIM.messenger.redrawTab[userId] = true;
				if (data.ERROR == 'ACCESS_DENIED' && this.BXIM.messenger.currentTab == userId)
				{
					if (BX.MessengerProxy)
					{
						BX.MessengerProxy.sendAccessDeniedErrorEvent(data.USER_ID);
					}
					this.BXIM.messenger.currentTab = 0;
					this.BXIM.messenger.openChatFlag = false;
					this.BXIM.messenger.openCallFlag = false;
					this.BXIM.messenger.openLinesFlag = false;
					this.BXIM.messenger.extraClose();
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function(){this.loadLastMessage(userId)}, this), 2000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR')
				{
					this.BXIM.messenger.sendAjaxTry++;
					if (this.isDesktop() || this.isMobile())
					{
						setTimeout(BX.delegate(function (){
							this.loadLastMessage(userId)
						}, this), 10000);
					}
					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
				callback(userId, false, data);
			}
		}, this);

		var readMessage = this.isMobile() || this.BXIM.isFocus();
		if (
			userIsChat &&
			this.BXIM.messenger.chat[chatId] &&
			this.BXIM.messenger.chat[chatId].owner == 0 &&
			this.BXIM.messenger.chat[chatId].type == 'lines'
		)
		{
			readMessage = false;
		}

		var xhr = BX.ajax({
			url: this.BXIM.pathToAjax+'?LOAD_LAST_MESSAGE&D='+userId+'&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			data: {
				'IM_LOAD_LAST_MESSAGE' : 'Y',
				'CHAT' : userIsChat? 'Y': 'N',
				'USER_ID' : userId,
				'USER_LOAD' : 'Y',
				'TAB' : this.BXIM.messenger.currentTab,
				//'READ' : readMessage? 'Y': 'N',
				'MOBILE' : this.isMobile()? 'Y': 'N',
				'FOCUS' : !this.isMobile() || typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground()? 'N': 'Y',
				'SEARCH_MARK' : !userIsChat && this.BXIM.messenger.users[userId] && this.BXIM.messenger.users[userId].search_mark? this.BXIM.messenger.users[userId].search_mark: '',
				'IM_AJAX_CALL' : 'Y',
				'sessid': BX.bitrix_sessid()
			},
			onsuccess: onsuccess,
			onprogress: function(data){
				if (data.position == 0 && data.totalSize == 0)
				{
					onfailure();
				}
			},
			onfailure: onfailure
		});
	};

	MessengerCommon.prototype.openDialog = function(dialogId, extraClose, callToggle)
	{
		var dialog = BX.MessengerCommon.getUserParam(dialogId);
		if (dialog.id <= 0)
			return false;

		dialogId = dialogId? dialogId: 0;

		var element = this.recentListGetItem(dialogId);
		if (element && element.unread)
		{
			this.recentListUpdateItem({
				id: dialogId,
				unread: false
			});
			this.recentListRedraw();

			BX.rest.callMethod('im.recent.unread', {'DIALOG_ID': dialogId, 'ACTION': 'N'});
		}

		this.BXIM.messenger.currentTab = dialogId;
		if (dialogId.toString().substr(0,4) == 'chat')
		{
			this.BXIM.messenger.openChatFlag = true;
			if (this.BXIM.messenger.chat[dialogId.toString().substr(4)] && this.BXIM.messenger.chat[dialogId.toString().substr(4)].type == 'call')
				this.BXIM.messenger.openCallFlag = true;
			else if (this.BXIM.messenger.chat[dialogId.toString().substr(4)] && this.BXIM.messenger.chat[dialogId.toString().substr(4)].type == 'lines')
			{
				if (!this.BXIM.bitrixOpenLines)
				{
					return false;
				}
				this.BXIM.messenger.openLinesFlag = true;
			}
		}

		BX.localStorage.set('mct', this.BXIM.messenger.currentTab, 15);

		if (this.isMobile())
		{
			this.BXIM.messenger.dialogStatusRedrawDelay();
		}
		else
		{
			this.BXIM.messenger.dialogStatusRedraw();
		}

		if (!this.isMobile())
		{
			if (this.BXIM.messenger.popupMessengerPanel)
			{
				this.BXIM.messenger.popupMessengerPanel.className  = this.BXIM.messenger.openChatFlag? 'bx-messenger-panel bx-messenger-hide': 'bx-messenger-panel';
			}

			if (this.BXIM.messenger.openChatFlag)
			{
				this.BXIM.messenger.popupMessengerPanelChat.className = this.BXIM.messenger.openCallFlag? 'bx-messenger-panel bx-messenger-hide': 'bx-messenger-panel';
				this.BXIM.messenger.popupMessengerPanelCall.className = this.BXIM.messenger.openCallFlag? 'bx-messenger-panel': 'bx-messenger-panel bx-messenger-hide';
			}
			else
			{
				this.BXIM.messenger.popupMessengerPanelChat.className = 'bx-messenger-panel bx-messenger-hide';
				this.BXIM.messenger.popupMessengerPanelCall.className = 'bx-messenger-panel bx-messenger-hide';
			}
		}

		extraClose = extraClose == true;
		callToggle = callToggle === true;

		var arMessage = [];
		if (typeof(this.BXIM.messenger.showMessage[dialogId]) != 'undefined' && this.BXIM.messenger.showMessage[dialogId].length > 0)
		{
			if (
				!this.isMobile()
				&& this.BXIM.messenger.showMessage[dialogId]
				&& this.BXIM.messenger.showMessage[dialogId].length != 0
				&& this.BXIM.messenger.showMessage[dialogId].length == BX.MessengerCommon.getCounter(dialogId)
			)
			{
				this.drawTab(dialogId, true);

				BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
				var loading = BX.create("div", { props : { className : "bx-notifier-content-link-history"}, children : [
					BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
					BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_LOAD_MESSAGE')})
				]});
				this.BXIM.messenger.redrawTab[dialogId] = true;

				this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(loading, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);

				if (this.isMobile())
				{
					setTimeout(BX.delegate(function(){this.BXIM.messenger.autoScroll()}, this), 100);
				}
			}
			else if (!dialog.fake && this.BXIM.messenger.showMessage[dialogId].length >= 15)
			{
				if (this.isMobile() && this.BXIM.webComponent)
				{
					this.drawTab(dialogId, true);
					this.BXIM.messenger.redrawTab[dialogId] = true;
				}
				else if (this.BXIM.messenger.redrawTab[dialogId])
				{
					this.drawTab(dialogId, true);
				}
				// else // TODO remove this later
				// {
				//	this.BXIM.messenger.redrawTab[dialogId] = false;
				// }
			}
			else
			{
				this.drawTab(dialogId, true);
				this.BXIM.messenger.redrawTab[dialogId] = true;
			}
		}
		else if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_ERROR")})
			]})];
			this.BXIM.messenger.redrawTab[dialogId] = true;
		}
		else if (typeof(this.BXIM.messenger.showMessage[dialogId]) == 'undefined')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_LOAD_MESSAGE')})
			]})];
			this.BXIM.messenger.redrawTab[dialogId] = true;
		}
		else if (this.BXIM.messenger.redrawTab[dialogId] && this.BXIM.messenger.showMessage[dialogId].length == 0)
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_MESSAGE")})
			]})];
			this.BXIM.messenger.showMessage[dialogId] = [];
		}
		else
		{
			var messageEmpty = "";
			if (this.isBot(dialogId) && this.BXIM.messenger.users[dialogId])
			{
				messageEmpty = BX.message("IM_M_NO_MESSAGE_BOT").replace('#BOT_NAME#', this.BXIM.messenger.users[dialogId].name);
			}
			else
			{
				messageEmpty = BX.message("IM_M_NO_MESSAGE");
			}
			BX.removeClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: messageEmpty})
			]})];
		}
		if (arMessage.length > 0)
		{
			this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';
			BX.adjust(this.BXIM.messenger.popupMessengerBodyWrap, {children: arMessage});
		}

		if (extraClose)
			this.BXIM.messenger.extraClose();

		if (this.isMobile())
		{
			BXMobileApp.UI.Page.TextPanel.setText(this.BXIM.messenger.textareaHistory[dialogId]? this.BXIM.messenger.textareaHistory[dialogId]: "");
		}
		else
		{
			this.BXIM.messenger.popupMessengerTextarea.value = this.BXIM.messenger.textareaHistory[dialogId]? this.BXIM.messenger.textareaHistory[dialogId]: "";
		}

		if (this.BXIM.messenger.redrawTab[dialogId])
		{
			this.loadLastMessage(dialogId);
		}
		else
		{
			this.drawTab(dialogId, true);
			if (this.isMobile())
			{
				this.BXIM.isFocusMobile(BX.delegate(function(visible){
					if (visible)
					{
						BX.MessengerCommon.readMessage(dialogId);
					}
				},this));
			}
			else if (this.BXIM.isFocus())
			{
				this.readMessage(dialogId);
			}
		}

		if (!this.isMobile())
			this.BXIM.messenger.resizeMainWindow();

		if (BX.MessengerCommon.countWriting(dialogId))
		{
			if (this.BXIM.messenger.openChatFlag)
				BX.MessengerCommon.drawWriting(0, dialogId);
			else
				BX.MessengerCommon.drawWriting(dialogId);
		}
		else if (this.BXIM.messenger.readedList[dialogId])
		{
			if (this.BXIM.messenger.openChatFlag)
			{
				this.drawReadMessageChat(dialogId, false);
			}
			else
			{
				this.drawReadMessage(dialogId, this.BXIM.messenger.readedList[dialogId].messageId, this.BXIM.messenger.readedList[dialogId].date, false);
			}
		}

		BX.onCustomEvent("onImDialogOpen", [{id: dialogId}]);
		if (this.isMobile())
		{
			BXMobileApp.onCustomEvent('onImDialogOpen', {'id': dialogId}, true);
		}
		else
		{
			this.BXIM.messenger.linesShowPromo();
			this.support24QuestionShowPromo();
		}
	};

	MessengerCommon.prototype.support24QuestionShowPromo = function()
	{
		clearTimeout(this.support24QuestionSchedulePromoTimeout);
		this.support24QuestionSchedulePromoTimeout = null;

		clearTimeout(this.support24QuestionShowPromoTimeout);
		this.support24QuestionShowPromoTimeout = null;

		if (
			!this.BXIM.messenger.currentTab
			|| !this.BXIM.messenger.bot[this.BXIM.messenger.currentTab]
			|| this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type !== 'support24'
			|| !BX.MessengerPromo
			|| typeof BX.MessengerPromo.show !== 'function'
		)
		{
			return false;
		}

		if (this.BXIM.messenger.popupMessengerTextarea.disabled)
		{
			this.support24QuestionSchedulePromoTimeout = setTimeout(this.support24QuestionShowPromo.bind(this), 5000);

			return true;
		}

		this.support24QuestionShowPromoTimeout = setTimeout(function () {
			var applicationButton =
				document.getElementsByClassName('bx-messenger-textarea-icon-marketplace-app-question')[0]
			;

			if (!applicationButton)
			{
				return;
			}

			BX.MessengerPromo.show(
				'imbot:support24:25112021:web',
				applicationButton,
				{ offsetLeft: 15 }
			);
		}, 20000);
	};

	MessengerCommon.prototype.drawTab = function(userId, scroll, messageCount, changeTab)
	{
		messageCount = messageCount || 0;
		changeTab = changeTab !== false;

		if (!userId)
		{
			userId = this.BXIM.messenger.currentTab;
		}

		if (this.BXIM.messenger.popupMessenger == null || userId != this.BXIM.messenger.currentTab)
			return false;

		if (typeof(this.messageGroup) != 'object')
		{
			this.messageGroup = {};
		}
		this.messageGroup['default'] = {};

		var openPageTabIm = true;

		if (this.BXIM.messenger.openChatFlag)
		{
			var chatId = userId.toString().substr(4);
			if (this.BXIM.messenger.chat[chatId])
			{
				if (this.BXIM.messenger.chat[chatId].type == 'open')
				{
					if (!BX.MessengerCommon.userInChat(chatId))
					{
						if (this.isMobile())
						{
							BXMobileApp.onCustomEvent('onPullExtendWatch', {'id': 'IM_PUBLIC_'+chatId, force: this.BXIM.messenger.redrawTab[userId]? false: true}, true);
						}
						else
						{
							BX.PULL.extendWatch('IM_PUBLIC_'+chatId, this.BXIM.messenger.redrawTab[userId]? false: true);
						}
					}
				}
				else if (this.BXIM.messenger.chat[chatId].type == 'lines' && this.isLinesOperator())
				{
					openPageTabIm = false;
				}
			}
		}

		if (this.isPage() && changeTab)
		{
			if (openPageTabIm)
			{
				if (BX.MessengerWindow.currentTab != 'im')
				{
					BX.MessengerWindow.changeTab('im');
				}
			}
			else
			{
				if (BX.MessengerWindow.currentTab != 'im-ol')
				{
					BX.MessengerWindow.changeTab('im-ol');
				}
			}
		}

		if (this.isMobile())
		{
			this.BXIM.messenger.dialogStatusRedrawDelay();
		}
		else
		{
			this.BXIM.messenger.dialogStatusRedraw();
		}
		this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';
		BX.removeClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');

		if (!this.BXIM.messenger.showMessage[userId] || this.BXIM.messenger.showMessage[userId].length <= 0)
		{
			var messageEmpty = "";
			var messageEmptyButton = null;
			if (this.isBot(userId) && this.BXIM.messenger.users[userId])
			{
				messageEmpty = BX.message("IM_M_NO_MESSAGE_BOT").replace('#BOT_NAME#', this.BXIM.messenger.users[userId].name);
			}
			else
			{
				messageEmpty = BX.message("IM_M_NO_MESSAGE");
				messageEmptyButton = BX.create('span', {props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
					BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_M_NO_MESSAGE_LOAD')})
				], events: {click: BX.delegate(function(){
					this.loadHistory(this.BXIM.messenger.currentTab, false, true);
				}, this)}});
			}
			this.BXIM.messenger.popupMessengerBodyWrap.appendChild(BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: messageEmpty}),
				messageEmptyButton
			]}));
		}

		if (this.BXIM.messenger.showMessage[userId])
			this.BXIM.messenger.showMessage[userId].sort(BX.delegate(function(i, ii) {if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = this.BXIM.messenger.message[i].date.getTime(); var i2 = this.BXIM.messenger.message[ii].date.getTime(); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
		else
			this.BXIM.messenger.showMessage[userId] = [];

		for (var i = 0; i < this.BXIM.messenger.showMessage[userId].length; i++)
		{
			if (
				this.isMobile()
				&& this.BXIM.webComponent
				&& this.BXIM.messenger.showMessage[userId][i].toString().indexOf('temp') == 0
			)
			{
				continue;
			}

			BX.MessengerCommon.drawMessage(userId, this.BXIM.messenger.message[this.BXIM.messenger.showMessage[userId][i]], false);
		}

		if (messageCount > 0 && messageCount < 30)
		{
			if (!this.BXIM.messenger.openChatFlag || this.BXIM.messenger.chat[userId.toString().substr(4)])
			{
				var skipButton = false;
				if (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[userId.toString().substr(4)].date_create)
				{
					if ((this.BXIM.messenger.chat[userId.toString().substr(4)].date_create.getTime()/1000)+2500000 > (new Date().getTime())/1000)
					{
						skipButton = true;
					}
				}
				if (!skipButton)
				{
					var messageEmptyButton = BX.create('span', {props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
						BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_M_NO_MESSAGE_LOAD')})
					], events: {click: BX.delegate(function(){
						this.loadHistory(this.BXIM.messenger.currentTab, false, true);
					}, this)}});
					this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(messageEmptyButton, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);
				}
			}
		}

		if (this.BXIM.messenger.chat[chatId])
		{
			if (this.BXIM.messenger.chat[chatId].entity_type == 'LINES')
			{
				var session = BX.MessengerCommon.linesGetSession(this.BXIM.messenger.chat[chatId]);
				var line;

				if (parseInt(session.id) > 0)
				{
					for (i=0; i < this.BXIM.messenger.openlines.queue.length; i++)
					{
						if (this.BXIM.messenger.openlines.queue[i].id == session.lineId)
						{
							line = this.BXIM.messenger.openlines.queue[i];
							break;
						}
					}

					if (line && line.queue_type == 'all')
					{
						if (!BX.MessengerCommon.isSessionBlocked(chatId))
						{
							BX.style(this.BXIM.messenger.popupMessengerTextareaOpenLinesSkip, 'display', 'none');
						}
						else
						{
							BX.style(this.BXIM.messenger.popupMessengerTextareaOpenLinesSkip, 'display', 'inline-block');
						}
					}
					else
					{
						BX.style(this.BXIM.messenger.popupMessengerTextareaOpenLinesSkip, 'display', 'inline-block');
					}
				}
			}
		}

		scroll = scroll != false;
		if (scroll)
		{
			if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
				this.BXIM.messenger.popupMessengerBodyAnimation.stop();

			if (userId != this.BXIM.userId && this.BXIM.messenger.unreadMessage[userId] && this.BXIM.messenger.unreadMessage[userId].length > 0)
			{
				var textElement = BX('im-message-'+this.BXIM.messenger.unreadMessage[userId][0]);
				if (textElement && textElement.parentNode.parentNode.parentNode.parentNode.parentNode)
				{
					this.scrollToNode(textElement.parentNode.parentNode.parentNode.parentNode.parentNode);
				}
				else
				{
					this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
				}
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}

		BX.onCustomEvent("onImDrawTab", [{id: userId, hasMessage: this.BXIM.messenger.showMessage[userId] && this.BXIM.messenger.showMessage[userId].length > 0}]);
		if (BX.MessengerProxy && BX.MessengerCommon.getCounter(userId) === 0)
		{
			BX.MessengerProxy.sendCounterChangeEvent(userId, 0);
		}

		if (BX.MessengerCommon.countWriting(userId))
		{
			if (this.BXIM.messenger.openChatFlag)
				BX.MessengerCommon.drawWriting(0, userId);
			else
				BX.MessengerCommon.drawWriting(userId);
		}
		else if (this.BXIM.messenger.readedList[userId])
		{
			if (this.BXIM.messenger.openChatFlag)
			{
				this.drawReadMessageChat(userId, false);
			}
			else
			{
				this.drawReadMessage(userId, this.BXIM.messenger.readedList[userId].messageId, this.BXIM.messenger.readedList[userId].date, false);
			}
		}

		if (this.BXIM.messenger.linesWritingList[chatId])
		{
			var chatName = 'chat' + chatId;
			if (chatName === BXIM.messenger.currentTab)
			{
				BX.MessengerCommon.drawMessage(BXIM.messenger.currentTab, BXIM.messenger.linesWritingList[chatId]);

				var createdMessage = BX.findChildByClassName(
					this.BXIM.messenger.popupMessengerBodyWrap,
					"bx-messenger-content-item-id-"+BXIM.messenger.linesWritingList[chatId].id
				);

				clearTimeout(this.BXIM.messenger.linesWritingListTimeout[chatId]);
				this.BXIM.messenger.linesWritingListTimeout[chatId] = setTimeout(BX.delegate(function(){
					BX.remove(createdMessage);
					delete this.BXIM.messenger.linesWritingList[chatId];
				}, this), 29500);
			}
		}

		//delete this.BXIM.messenger.redrawTab[userId]; // TODO remove this later
	};


	MessengerCommon.prototype.scrollToNode = function(node)
	{
		var obNode = BX(node);

		var isEdge = navigator.userAgent.indexOf('Edge') > -1;
		if (!isEdge && obNode.scrollIntoView)
		{
			if (this.BXIM.options.v2layout)
			{
				obNode.scrollIntoView({ behavior: "auto", block: "nearest" });
			}
			else
			{
				obNode.scrollIntoView(true);
			}
		}
		else
		{
			var arNodePos = BX.pos(obNode);
			window.scrollTo(arNodePos.left, arNodePos.top);
		}
	}

	/* Section: Send Message */
	MessengerCommon.prototype.sendMessageAjax = function(messageTmpIndex, recipientId, messageText, sendMessageToChat, olSilentMode)
	{
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
			return false;

		BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex);

		if (this.BXIM.messenger.sendMessageFlag < 0)
			this.BXIM.messenger.sendMessageFlag = 0;

		clearTimeout(this.BXIM.messenger.sendMessageTmpTimeout['temp'+messageTmpIndex]);
		if (this.BXIM.messenger.sendMessageTmp[messageTmpIndex])
			return false;

		this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = true;
		sendMessageToChat = sendMessageToChat == true;
		this.BXIM.messenger.sendMessageFlag++;

		if (typeof olSilentMode === 'boolean')
		{
			olSilentMode = olSilentMode? 'Y': 'N';
		}
		else
		{
			olSilentMode = 'N';
			if (sendMessageToChat && this.BXIM.messenger.linesSilentMode && this.BXIM.messenger.linesSilentMode[recipientId.toString().substr(4)])
			{
				olSilentMode = 'Y';
			}
		}

		this.recentListAddItem({
			id: recipientId,
			message: {
				id: 'temp'+messageTmpIndex,
				date: new Date(),
				author_id: this.BXIM.userId,
				status: 'received',
				text: BX.util.htmlspecialchars(messageText),
				attach: false,
				file: false,
			},
		});
		this.recentListRedraw();
		this.BXIM.messenger.updateMessageCount();

		BX.onCustomEvent('onImBeforeMessageSend', [{recipientId: recipientId, messageText: messageText}]);

		if (BX.MessengerProxy)
		{
			BX.MessengerProxy.sendSetMessageEvent({
				id: 'temp'+messageTmpIndex,
				dialogId: recipientId,
				text: messageText,
				date: new Date()
			});
		}
		var _ajax = BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_SEND&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'im.message.add',
				dialog: BX.MessengerCommon.getDialogDataForTracking(recipientId)
			}),
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 120,
			data: {'IM_SEND_MESSAGE' : 'Y', 'CHAT': sendMessageToChat? 'Y': 'N', 'ID' : 'temp'+messageTmpIndex, 'RECIPIENT_ID' : recipientId, 'MESSAGE' : messageText, 'OL_SILENT': olSilentMode, 'TAB' : this.BXIM.messenger.currentTab, 'USER_TZ_OFFSET': BX.message('USER_TZ_OFFSET'), 'IM_AJAX_CALL' : 'Y', 'FOCUS' : !this.isMobile() || typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground()? 'N': 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (this.isMobile() && typeof fabric != 'undefined')
				{
					fabric.Answers.sendCustomEvent("imMessageSend", {});
				}
				this.BXIM.messenger.sendMessageFlag--;

				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}

				if (data && data.ERROR == '')
				{
					this.BXIM.messenger.sendAjaxTry = 0;

					if (this.BXIM.messenger.message[data.TMP_ID])
					{
						this.BXIM.messenger.message[data.TMP_ID].date = new Date(data.SEND_DATE);
						this.BXIM.messenger.message[data.TMP_ID].textOriginal = data.SEND_MESSAGE;
						this.BXIM.messenger.message[data.TMP_ID].text = BX.MessengerCommon.prepareText(data.SEND_MESSAGE, true, true, true);
						this.BXIM.messenger.message[data.TMP_ID].id = data.ID;

						if (data.SEND_MESSAGE_PARAMS)
						{
							this.BXIM.messenger.message[data.TMP_ID].params = data.SEND_MESSAGE_PARAMS;
						}

						this.BXIM.messenger.message[data.ID] = this.BXIM.messenger.message[data.TMP_ID];
						delete this.BXIM.messenger.message[data.TMP_ID]
					}

					for (var i in data.SEND_MESSAGE_FILES)
					{
						if (!this.BXIM.messenger.disk.files[data.CHAT_ID])
							this.BXIM.messenger.disk.files[data.CHAT_ID] = {};
						if (this.BXIM.messenger.disk.files[data.CHAT_ID][i])
							continue;

						data.SEND_MESSAGE_FILES[i].date = new Date(data.SEND_MESSAGE_FILES[i].date);
						this.BXIM.messenger.disk.files[data.CHAT_ID][i] = data.SEND_MESSAGE_FILES[i];
					}

					if (this.BXIM.messenger.popupMessengerLastMessage == data.TMP_ID)
						this.BXIM.messenger.popupMessengerLastMessage = data.ID;

					var message = this.BXIM.messenger.message[data.ID];

					this.BXIM.messenger.showMessage[data.RECIPIENT_ID] = this.BXIM.messenger.showMessage[data.RECIPIENT_ID].filter(function(element){
						return element != data.TMP_ID && element != data.ID
					});
					this.BXIM.messenger.showMessage[data.RECIPIENT_ID].push(data.ID);

					var item = this.BXIM.messenger.recent.find(function(item) {
						return item.message.id == data.TMP_ID;
					});
					if (item)
					{
						item.message.id = ''+data.ID+'';
					}

					if (data.RECIPIENT_ID == this.BXIM.messenger.currentTab)
					{
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.TMP_ID+''}}, true);
						if (!element)
						{
							element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.ID+''}}, true);
						}
						if (element)
						{
							element.setAttribute('data-messageid',	''+data.ID+'');
							if (element.getAttribute('data-blockmessageid') == ''+data.TMP_ID+'')
							{
								element.setAttribute('data-blockmessageid', ''+data.ID+'');
							}
							else
							{
								var element2 = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+data.TMP_ID+''}}, true);
								if (element2)
								{
									element2.setAttribute('data-blockmessageid', ''+data.ID+'');
								}
							}
							var element3 = BX.findChild(element, {attribute: {'data-messageid': ''+data.TMP_ID+''}}, true);
							if (element3)
							{
								element3.setAttribute('data-messageid', ''+data.ID+'');
							}
						}

						var messageKeyboardBox = BX('im-message-keyboard-'+data.TMP_ID);
						if (messageKeyboardBox)
						{
							messageKeyboardBox.id = 'im-message-keyboard-'+data.ID;
						}
						else
						{
							messageKeyboardBox = BX('im-message-keyboard-empty-'+data.TMP_ID);
							if (messageKeyboardBox)
							{
								messageKeyboardBox.id = 'im-message-keyboard-empty-'+data.ID;
							}
						}

						BX.MessengerCommon.clearProgessMessage(data.TMP_ID);

						var textElement = BX('im-message-'+data.TMP_ID);
						if (!textElement)
						{
							textElement = BX('im-message-'+data.ID);
						}
						if (textElement)
						{
							textElement.id = 'im-message-'+data.ID;
							var objectReference = {oneSmileInMessage: false};
							textElement.innerHTML = BX.MessengerCommon.prepareText(data.SEND_MESSAGE, true, true, true, null, objectReference);
							if (objectReference.oneSmileInMessage)
							{
								var elementContent = BX.findChildByClassName(element, "bx-messenger-content-item-content");
								if (elementContent)
								{
									BX.addClass(elementContent, 'bx-messenger-content-item-content-transparent');
								}
							}
						}

						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
							lastMessageElementDate.innerHTML = BX.MessengerCommon.formatDate(message.date, BX.MessengerCommon.getDateFormatType('MESSAGE'));
					}

					if (!this.BXIM.messenger.history[data.RECIPIENT_ID])
					{
						this.BXIM.messenger.history[data.RECIPIENT_ID] = [];
					}
					this.BXIM.messenger.history[data.RECIPIENT_ID] = this.BXIM.messenger.history[data.RECIPIENT_ID].filter(function(element){
						return element != message.id
					});
					this.BXIM.messenger.history[data.RECIPIENT_ID].push(message.id);

					this.BXIM.messenger.updateStateVeryFastCount = 2;
					this.BXIM.messenger.updateStateFastCount = 5;
					this.BXIM.messenger.setUpdateStateStep();

					if (data.SEND_MESSAGE_PARAMS)
					{
						if (data.SEND_MESSAGE_PARAMS.URL_ONLY == 'Y' && this.BXIM.settings.enableRichLink)
						{
							BX.addClass(element.firstElementChild, 'bx-messenger-content-item-content-rich-link');
						}
						if (data.SEND_MESSAGE_PARAMS.LARGE_FONT == 'Y' && this.BXIM.settings.enableBigSmile)
						{
							BX.addClass(element.firstElementChild, 'bx-messenger-content-item-content-large-font');
						}

						if (data.RECIPIENT_ID.toString().substr(0,4) == 'chat')
						{
							if (this.isMobile())
							{
								BX.onCustomEvent(window, "onPull-im", [{
									command: "messageParamsUpdate",
									params: {
										"id": data.ID,
										"type":'chat',
										"chatId":data.CHAT_ID,
										"senderId":data.SENDER_ID,
										"params":data.SEND_MESSAGE_PARAMS,
										"animation": 'N'
									},
									extra: {
										"revision_im_web": this.BXIM.revision,
										"revision_im_mobile": this.BXIM.revision
									}
								}]);
							}
							else
							{
								BX.onCustomEvent(window, "onPullEvent-im", ["messageParamsUpdate", {
									"id": data.ID,
									"type":'chat',
									"chatId":data.CHAT_ID,
									"senderId":data.SENDER_ID,
									"params":data.SEND_MESSAGE_PARAMS,
									"animation": 'N'
								}, {
									"revision_im_web": this.BXIM.revision,
									"revision_im_mobile": this.BXIM.revision
								}]);
							}
						}
						else
						{
							if (this.isMobile())
							{
								BX.onCustomEvent(window, "onPull-im", [{
									command: "messageParamsUpdate",
									params: {
										"id": data.ID,
										"type":'private',
										"chatId":data.CHAT_ID,
										"fromUserId":data.SENDER_ID,
										"toUserId":data.RECIPIENT_ID,
										"senderId":data.SENDER_ID,
										"params":data.SEND_MESSAGE_PARAMS,
										"animation": 'N'
									},
									extra: {
										"revision_im_web": this.BXIM.revision,
										"revision_im_mobile": this.BXIM.revision,
									}
								}]);
							}
							else
							{
								BX.onCustomEvent(window, "onPullEvent-im", ["messageParamsUpdate", {
									"id": data.ID,
									"type":'private',
									"chatId":data.CHAT_ID,
									"fromUserId":data.SENDER_ID,
									"toUserId":data.RECIPIENT_ID,
									"senderId":data.SENDER_ID,
									"params":data.SEND_MESSAGE_PARAMS,
									"animation": 'N'
								}, {
									"revision_im_web": this.BXIM.revision,
									"revision_im_mobile": this.BXIM.revision
								}]);
							}
						}
					}

					BX.MessengerCommon.updateStateVar(data, true, true);
					BX.localStorage.set('msm2', {'id': data.ID, 'recipientId': data.RECIPIENT_ID, 'date': data.SEND_DATE, 'text' : data.SEND_MESSAGE, 'senderId' : this.BXIM.userId, 'MESSAGE': data.MESSAGE, 'USERS_MESSAGE': data.USERS_MESSAGE, 'USERS': data.USERS, 'USER_IN_GROUP': data.USER_IN_GROUP}, 5);

					if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
					}
					else if (this.BXIM.animationSupport)
					{
						if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
							this.BXIM.messenger.popupMessengerBodyAnimation.stop();
						(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
							duration : 800,
							start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
							finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
							transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
							step : BX.delegate(function(state){
								this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
							}, this)
						})).animate();
					}
					else
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
					}

					//if (this.MobileActionEqual('RECENT') && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
					//	this.recentListRedraw();
				}
				else
				{
					if (data && data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(function(){
							this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
							this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
						}, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data && data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.isDesktop() || this.isMobile())
						{
							setTimeout(BX.delegate(function (){
								this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
								this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
							}, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
					else
					{
						this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': 'temp'+messageTmpIndex}}, true);
						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						console.warn(lastMessageElementDate);
						if (lastMessageElementDate)
						{
							if (data.ERROR == 'SESSION_ERROR' || data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'UNKNOWN_ERROR' || data.ERROR == 'IM_MODULE_NOT_INSTALLED')
								lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');
							else
								lastMessageElementDate.innerHTML = data.ERROR;
						}
						BX.onCustomEvent(window, 'onImError', ['SEND_ERROR', data.ERROR, data.TMP_ID, data.SEND_DATE, data.SEND_MESSAGE, data.RECIPIENT_ID]);

						console.log('temp'+messageTmpIndex);
						BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex, {title: BX.message('IM_M_RETRY'), chat: sendMessageToChat? 'Y':'N'});

						if (this.BXIM.messenger.message['temp'+messageTmpIndex])
							this.BXIM.messenger.message['temp'+messageTmpIndex].retry = true;
					}
				}
			}, this),
			onfailure: BX.delegate(function()	{
				this.BXIM.messenger.sendMessageFlag--;
				this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
				var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': 'temp'+messageTmpIndex}}, true);
				var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
				if (lastMessageElementDate)
					lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');

				BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex, {title: BX.message('IM_M_RETRY'), chat: sendMessageToChat? 'Y':'N'});

				this.BXIM.messenger.sendAjaxTry = 0;
				try {
					if (typeof(_ajax) == 'object' && _ajax.status == 0)
						BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
				}
				catch(e) {}
				if (this.BXIM.messenger.message['temp'+messageTmpIndex])
					this.BXIM.messenger.message['temp'+messageTmpIndex].retry = true;
			}, this)
		});
	};

	MessengerCommon.prototype.sendMessageRetry = function()
	{
		var currentTab = this.BXIM.messenger.currentTab;
		var messageStack = [];
		for (var i = 0; i < this.BXIM.messenger.showMessage[currentTab].length; i++)
		{
			var message = this.BXIM.messenger.message[this.BXIM.messenger.showMessage[currentTab][i]];
			if (!message || message.id.toString().indexOf('temp') != 0)
				continue;

			message.text = message.textOriginal;
			if (!message.text)
				continue;

			messageStack.push(message);
		}
		if (messageStack.length <= 0)
			return false;

		messageStack.sort(function(i, ii) {i = i.id.substr(4); ii = ii.id.substr(4); if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}});
		for (var i = 0; i < messageStack.length; i++)
		{
			this.sendMessageRetryTimeout(messageStack[i], 100*i);
		}
	};

	MessengerCommon.prototype.sendMessageRetryTimeout = function(message, timeout)
	{
		var messageLinesHidden = undefined;
		if (message.params && message.params.CLASS === "bx-messenger-content-item-system")
		{
			messageLinesHidden = true;
		}
		clearTimeout(this.BXIM.messenger.sendMessageTmpTimeout[message.id]);
		this.BXIM.messenger.sendMessageTmpTimeout[message.id] = setTimeout(BX.delegate(function() {
			BX.MessengerCommon.sendMessageAjax(
				message.id.substr(4),
				message.recipientId,
				message.text,
				message.recipientId.toString().substr(0,4) == 'chat',
				messageLinesHidden
			);
		}, this), timeout);
	};

	MessengerCommon.prototype.getLastMessageInDialog = function(dialogId)
	{
		var result = false;

		if (this.BXIM.messenger.showMessage[dialogId] && this.BXIM.messenger.showMessage[dialogId].length > 0)
		{
			var lastId = this.BXIM.messenger.showMessage[dialogId][this.BXIM.messenger.showMessage[dialogId].length-1];
			result = this.BXIM.messenger.message[lastId];
		}

		return result;
	}

	MessengerCommon.prototype.joinToChat = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
		{
			return false;
		}

		if (
			this.BXIM.messenger.chat[chatId]
			&& !(
				this.BXIM.messenger.chat[chatId].type == 'open'
				|| this.BXIM.messenger.chat[chatId].type == 'announcement'
			)
		)
		{
			return false;
		}

		if (BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_JOIN&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 60,
			data: {'IM_CHAT_JOIN' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;

				this.BXIM.messenger.popupMessengerTextarea.disabled = false;
				this.BXIM.messenger.popupMessengerTextarea.focus();
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.messageUrlAttachDelete = function(messageId, attachId)
	{
		if (
			messageId.toString().substr(0,4) == 'temp'
			|| !this.BXIM.messenger.message[messageId]
			|| !this.BXIM.messenger.message[messageId].params
			|| !this.BXIM.messenger.message[messageId].params.ATTACH
			|| !this.BXIM.messenger.message[messageId].params.URL_ID
			|| this.BXIM.messenger.message[messageId].params.URL_ID.indexOf(parseInt(attachId)) == -1 && this.BXIM.messenger.message[messageId].params.URL_ID.indexOf(attachId.toString()) == -1
		)
		{
			return false;
		}

		for (var i = 0; i < this.BXIM.messenger.message[messageId].params.ATTACH.length; i++)
		{
			if (!this.BXIM.messenger.message[messageId].params.ATTACH[i])
				continue;

			if (this.BXIM.messenger.message[messageId].params.ATTACH[i].ID == attachId)
			{
				delete this.BXIM.messenger.message[messageId].params.ATTACH[i];
				break;
			}
		}
		for (var i = 0; i < this.BXIM.messenger.message[messageId].params.URL_ID.length; i++)
		{
			if (!this.BXIM.messenger.message[messageId].params.URL_ID[i])
				continue;

			if (this.BXIM.messenger.message[messageId].params.URL_ID[i] == attachId)
			{
				delete this.BXIM.messenger.message[messageId].params.URL_ID[i];
				break;
			}
		}

		var messageBox = BX('im-message-'+messageId);
		var attachNode = BX.MessengerCommon.drawAttach(messageId, this.BXIM.messenger.message[messageId].chatId, this.BXIM.messenger.message[messageId].params.ATTACH);
		messageBox.nextElementSibling.innerHTML = '';
		if (attachNode.length > 0)
		{
			BX.adjust(messageBox.nextElementSibling, {children: attachNode});
		}
		if (attachNode.length <= 0)
		{
			BX.removeClass(messageBox.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-rich-link');
		}

		BX.ajax({
			url: this.BXIM.pathToAjax+'?URL_ATTACH_DELETE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_URL_ATTACH_DELETE' : 'Y', 'ID': messageId, 'ATTACH_ID' : attachId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});

		return true;
	}

	MessengerCommon.prototype.messageLike = function(messageId, onlyDraw)
	{
		if (
			messageId.toString().substr(0,4) == 'temp'
			|| !this.BXIM.messenger.message[messageId]
			|| this.BXIM.messenger.popupMessengerLikeBlock[messageId]
		)
		{
			return false;
		}

		onlyDraw = typeof(onlyDraw) == 'undefined'? false: onlyDraw;

		if (!this.BXIM.messenger.message[messageId].params)
		{
			this.BXIM.messenger.message[messageId].params = {};
		}
		if (!this.BXIM.messenger.message[messageId].params.LIKE)
		{
			this.BXIM.messenger.message[messageId].params.LIKE = [];
		}

		var iLikeThis = BX.util.in_array(this.BXIM.userId, this.BXIM.messenger.message[messageId].params.LIKE);
		if (!onlyDraw)
		{
			var likeAction = iLikeThis? 'minus': 'plus';
			if (likeAction == 'plus')
			{
				this.BXIM.messenger.message[messageId].params.LIKE.push(this.BXIM.userId);
				iLikeThis = true;
			}
			else
			{
				var newLikeArray = [];
				for (var i = 0; i < this.BXIM.messenger.message[messageId].params.LIKE.length; i++)
				{
					if (this.BXIM.messenger.message[messageId].params.LIKE[i] != this.BXIM.userId)
					{
						newLikeArray.push(this.BXIM.messenger.message[messageId].params.LIKE[i])
					}
				}
				this.BXIM.messenger.message[messageId].params.LIKE = newLikeArray;
				iLikeThis = false;
			}
		}
		var likeCount = this.BXIM.messenger.message[messageId].params.LIKE.length > 0? this.BXIM.messenger.message[messageId].params.LIKE.length: '';

		if (BX('im-message-'+messageId))
		{
			var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+messageId+''}}, false);
			var elementLike = BX.findChildByClassName(element, "bx-messenger-content-item-like");
			var elementLikeDigit = BX.findChildByClassName(element, "bx-messenger-content-like-digit", false);

			if (iLikeThis)
			{
				BX.addClass(elementLike, 'bx-messenger-content-item-liked');
			}
			else
			{
				BX.removeClass(elementLike, 'bx-messenger-content-item-liked');
			}

			if (likeCount>0)
			{
				elementLikeDigit.setAttribute('title', BX.message('IM_MESSAGE_LIKE_LIST'));
				BX.removeClass(elementLikeDigit.parentNode, 'bx-messenger-content-like-digit-off');
			}
			else
			{
				elementLikeDigit.setAttribute('title', '');
				BX.addClass(elementLikeDigit.parentNode, 'bx-messenger-content-like-digit-off');
			}

			elementLikeDigit.innerHTML = likeCount;
		}

		if (this.isMobile())
		{
			app.exec("callVibration");
		}

		if (!onlyDraw)
		{
			clearTimeout(this.BXIM.messenger.popupMessengerLikeBlockTimeout[messageId]);
			this.BXIM.messenger.popupMessengerLikeBlockTimeout[messageId] = setTimeout(BX.delegate(function(){
				this.BXIM.messenger.popupMessengerLikeBlock[messageId] = true;
				BX.ajax({
					url: this.BXIM.pathToAjax+'?MESSAGE_LIKE&V='+this.BXIM.revision,
					method: 'POST',
					dataType: 'json',
					skipAuthCheck: true,
					timeout: 30,
					data: {'IM_LIKE_MESSAGE' : 'Y', 'ID': messageId, 'ACTION' : likeAction, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
					onsuccess: BX.delegate(function(data) {
						if (data.ERROR == '')
						{
							this.BXIM.messenger.message[messageId].params.LIKE = data.LIKE;
						}
						this.BXIM.messenger.popupMessengerLikeBlock[messageId] = false;
						BX.MessengerCommon.messageLike(messageId, true);
					}, this),
					onfailure: BX.delegate(function(data) {
						this.BXIM.messenger.popupMessengerLikeBlock[messageId] = false;
					}, this)
				});
			},this), 1000);
		}

		return true;
	}

	MessengerCommon.prototype.messageIsLike = function(messageId)
	{
		return (
			this.BXIM.messenger.message[messageId]
			&& this.BXIM.messenger.message[messageId].params
			&& typeof(this.BXIM.messenger.message[messageId].params.LIKE) == "object"
			&& BX.util.in_array(this.BXIM.userId, this.BXIM.messenger.message[messageId].params.LIKE)
		);
	};

	MessengerCommon.prototype.checkEditMessage = function(id, type)
	{
		type = type || 'list';

		if (this.BXIM.messenger.openLinesFlag)
		{
			var olSource = this.linesGetSource(this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)]);
		}

		var result = false;
		if (
			this.BXIM.messenger.bot[this.BXIM.messenger.currentTab]
			&& (
				this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'network'
				&& this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'support24'
			)
		)
		{
			return result;
		}

		if (
			this.BXIM.ppServerStatus
			&& parseInt(id) != 0
			&& id.toString().substr(0,4) != 'temp'
			&& this.BXIM.messenger.message[id]
			&& (this.BXIM.messenger.message[id].date.getTime()/1000)+259200 > (new Date().getTime())/1000
			&& (
				!this.BXIM.messenger.message[id].params
				|| this.BXIM.messenger.message[id].params.IS_DELETED != 'Y'
			)
			&& BX('im-message-'+id)
			&& BX.util.in_array(id, this.BXIM.messenger.showMessage[this.BXIM.messenger.currentTab])
		)
		{
			if (this.BXIM.messenger.openLinesFlag)
			{
				if (this.BXIM.messenger.message[id].senderId == this.BXIM.userId)
				{
					if(type == 'edit')
					{
						result = this.BXIM.messenger.openlines.canUpdateOwnMessage.indexOf(olSource) > -1;
					}
					else if(type == 'delete')
					{
						result = this.BXIM.messenger.openlines.canDeleteOwnMessage.indexOf(olSource) > -1;
					}
				}
				else if (this.BXIM.messenger.openlines.canDeleteMessage.indexOf(olSource) > -1 && type == 'delete')
				{
					result = true;
				}
				if (result && olSource != 'network')
				{
					if (
						this.BXIM.messenger.message[id].params
						&& this.BXIM.messenger.message[id].params.CLASS === "bx-messenger-content-item-system"
					)
					{
						return true;
					}
					if (
						!this.BXIM.messenger.message[id].params
						|| typeof(this.BXIM.messenger.message[id].params.CONNECTOR_MID) == 'undefined'
						|| this.BXIM.messenger.message[id].params.CONNECTOR_MID.length <= 0
					)
					{
						result = false;
					}
				}
			}
			else if (this.BXIM.messenger.message[id].senderId == this.BXIM.userId)
			{
				result = true;
			}
		}

		return result;
	}

	MessengerCommon.prototype.editMessageAjax = function(id, text)
	{
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
			return false;

		this.BXIM.messenger.editMessageCancel();
		if (!BX.MessengerCommon.checkEditMessage(id, 'edit'))
			return false;

		if (text == this.BXIM.messenger.message[id].textOriginal)
			return false;

		text = text.replace('    ', "\t");
		text = BX.util.trim(text);
		if (text.length <= 0)
		{
			BX.MessengerCommon.deleteMessageAjax(id);
			return false;
		}

		this.BXIM.messenger.message[id].text = BX.MessengerCommon.prepareText(text, true, true, true);
		this.BXIM.messenger.message[id].textOriginal = text;

		text = BX.MessengerCommon.prepareMention(this.BXIM.messenger.currentTab, text);

		BX.MessengerCommon.drawProgessMessage(id);

		if (BX.MessengerProxy)
		{
			BX.MessengerProxy.sendSetMessageEvent({
				id: +id,
				dialogId: this.BXIM.messenger.message[id].recipientId,
				text: this.BXIM.messenger.message[id].textOriginal
			});
		}
		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_EDIT&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'im.message.update',
				dialog: BX.MessengerCommon.getDialogDataForTracking(this.BXIM.messenger.message[id].recipientId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_EDIT_MESSAGE' : 'Y', ID: id, MESSAGE: text, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this),
			onfailure: BX.delegate(function() {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this)
		});
	}

	MessengerCommon.prototype.deleteMessageAjax = function(id)
	{
		this.BXIM.messenger.editMessageCancel();

		if (
			this.BXIM.isAdmin &&
			this.BXIM.messenger.openChatFlag &&
			this.BXIM.messenger.message[id].chatId && this.BXIM.messenger.generalChatId == this.BXIM.messenger.message[id].chatId
		)
		{
		}
		else if (!BX.MessengerCommon.checkEditMessage(id, 'delete'))
		{
			return false;
		}

		BX.MessengerCommon.drawProgessMessage(id);

		if (BX.MessengerProxy)
		{
			BX.MessengerProxy.sendSetMessageEvent({
				id: +id,
				dialogId: this.BXIM.messenger.message[id].recipientId,
				text: this.BXIM.messenger.message[id].textOriginal
			});
		}
		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_DELETE&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'im.message.delete',
				dialog: BX.MessengerCommon.getDialogDataForTracking(this.BXIM.messenger.message[id].recipientId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_DELETE_MESSAGE' : 'Y', ID: id, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				if (data.ERROR)
					return false;

				if (this.BXIM.messenger.message[id])
				{
					this.BXIM.messenger.message[id].isNowDeleted = true;
				}

				BX.MessengerCommon.clearProgessMessage(id);
			}, this),
			onfailure: BX.delegate(function() {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this)
		});

		return true;
	}

	MessengerCommon.prototype.shareMessageAjax = function(id, type, date)
	{
		BX.MessengerCommon.drawProgessMessage(id);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_SHARE&TYPE='+type+'&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'im.message.share',
				dialog: BX.MessengerCommon.getDialogDataForTracking(this.BXIM.messenger.message[id].recipientId),
				data: {
					timShareType: type.toString().toLowerCase()
				}
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_SHARE_MESSAGE' : 'Y', ID: id, TYPE: type, DATE: date? date: 0, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				BX.MessengerCommon.clearProgessMessage(id);

				if (data.ERROR)
				{
					if (type === 'POST')
					{
						BX.UI.Notification.Center.notify({
							content: BX.message('IM_SHARE_POST_ERROR'),
							autoHideDelay: 2000
						});
					}

					return false;
				}
			}, this),
			onfailure: BX.delegate(function() {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this)
		});

		return true;
	}



	/* Section: keyboard */
	MessengerCommon.prototype.drawKeyboard = function(dialogId, messageId, buttonConfig)
	{
		if (!buttonConfig || buttonConfig == 'N')
		{
			keyboardNode = BX.create("div", {
				attrs : {id : "im-message-keyboard-empty-"+messageId},
			});
			return keyboardNode;
		}

		var keyboardNode = null;
		var keyboardButtons = [];
		var keyboardButton = null;
		var buttonValue = null;

		for (var i = 0; i < buttonConfig.length; i++)
		{
			if (buttonConfig[i].TYPE == 'NEWLINE')
			{
				keyboardButton = BX.create("div", {props : { className: "bx-messenger-keyboard-new-line"}});
			}
			else
			{
				if (
					buttonConfig[i].CONTEXT &&
					(
						this.isMobile() && buttonConfig[i].CONTEXT == 'DESKTOP' ||
						!this.isMobile() && buttonConfig[i].CONTEXT == 'MOBILE'
					)
				)
				{
					continue;
				}

				var textStyles = '';
				if (buttonConfig[i].WIDTH)
				{
					textStyles = textStyles+'width: '+buttonConfig[i].WIDTH+'px;';
				}
				else if (buttonConfig[i].DISPLAY == 'BLOCK')
				{
					textStyles = textStyles+'width: 225px;';
				}
				if (buttonConfig[i].BG_COLOR)
				{
					textStyles = textStyles+'background-color: '+buttonConfig[i].BG_COLOR+';';
				}
				if (buttonConfig[i].TEXT_COLOR)
				{
					textStyles = textStyles+'color: '+buttonConfig[i].TEXT_COLOR+';';
				}

				if (buttonConfig[i].DISABLED && buttonConfig[i].DISABLED == 'Y')
				{
					buttonValue = '<span class="bx-messenger-keyboard-button-text bx-messenger-keyboard-button-disabled" data-disabled="Y" style="'+textStyles+'">'+
						BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
					'</span>';
				}
				else
				{
					if (buttonConfig[i].LINK)
					{
						buttonValue = '<a href="'+buttonConfig[i].LINK+'" target="_blank" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
						'</a>';
					}
					else if (buttonConfig[i].FUNCTION)
					{
						var userFunc = buttonConfig[i].FUNCTION.toString().replace('#MESSAGE_ID#', messageId).replace('#DIALOG_ID#', dialogId).replace('#USER_ID#', this.BXIM.userId);
						buttonValue = '<a href="javascript:void(1);" onclick="'+userFunc+'; BX.PreventDefault(event);" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
						'</a>';
					}
					else if (
						buttonConfig[i].ACTION
						&& buttonConfig[i].ACTION_VALUE.toString()
					)
					{
						buttonValue = '<a href="javascript:void(1);" onclick="BX.MessengerCommon.executeParamsButton(\'KEYBOARD\', '+messageId+', '+i+', event);" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
						'</a>';
					}
					else if (buttonConfig[i].APP_ID)
					{
						buttonConfig[i].APP_PARAMS = buttonConfig[i].APP_PARAMS? buttonConfig[i].APP_PARAMS: '';
						buttonValue = '<a href="javascript:void(1);" onclick="BXIM.messenger.textareaIconDialogClick('+parseInt(buttonConfig[i].APP_ID)+', '+messageId+', \''+(BX.util.htmlspecialchars(buttonConfig[i].APP_PARAMS))+'\'); BX.PreventDefault(event);" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
						'</a>';
					}
					else
					{
						buttonValue = '<span class="bx-messenger-keyboard-button-text" data-dialogId="'+dialogId+'" data-messageId="'+messageId+'" data-blockAfterClick="'+buttonConfig[i].BLOCK+'" data-command="'+BX.util.htmlspecialchars(buttonConfig[i].COMMAND)+'" data-commandParams="'+BX.util.htmlspecialchars(buttonConfig[i].COMMAND_PARAMS)+'" data-botId="'+buttonConfig[i].BOT_ID+'" style="'+textStyles+'">'+
							BX.util.htmlspecialchars(buttonConfig[i].TEXT)+
						'</span>';
					}
				}

				keyboardButton = BX.create("span", {
					props : { className: "bx-messenger-keyboard-button bx-messenger-keyboard-button-"+(buttonConfig[i].DISPLAY.toLowerCase())},
					children: [buttonValue]
				});
			}
			keyboardButtons.push(keyboardButton);
		}

		if (keyboardButtons.length > 0)
		{
			keyboardNode = BX.create("div", {
				attrs : { id: "im-message-keyboard-"+messageId},
				props : { className: "bx-messenger-keyboard"},
				children: keyboardButtons
			});
		}
		else
		{
			keyboardNode = BX.create("div", {
				attrs : {id : "im-message-keyboard-empty-"+messageId},
			});
		}

		return keyboardNode;
	}

	MessengerCommon.prototype.executeParamsButton = function(type, messageId, index)
	{
		if (
			!this.BXIM.messenger.message[messageId]
			|| !this.BXIM.messenger.message[messageId].params[type]
			|| !this.BXIM.messenger.message[messageId].params[type][index]
		)
		{
			return false;
		}

		var button = this.BXIM.messenger.message[messageId].params[type][index];
		if (button.ACTION)
		{
			if (button.ACTION === 'SEND')
			{
				this.BXIM.sendMessage(this.BXIM.messenger.currentTab, button.ACTION_VALUE);
			}
			else if (button.ACTION === 'PUT')
			{
				this.BXIM.putMessage(button.ACTION_VALUE);
			}
			else if (button.ACTION === 'CALL')
			{
				this.BXIM.phoneTo(button.ACTION_VALUE);
			}
			else if (button.ACTION === 'HELP')
			{
				if (button.ACTION_VALUE !== '' && button.ACTION_VALUE !== '-')
				{
					BX.Helper.show('redirect=detail&HD_ID=' + button.ACTION_VALUE);
				}
				else
				{
					BX.Helper.show();
				}
			}
			else if (button.ACTION === 'COPY')
			{
				if (this.isMobile())
				{
					app.exec("copyToClipboard", {text: button.ACTION_VALUE});

					(new BXMobileApp.UI.NotificationBar({
						message: BX.message("IM_COPIED"),
						color: "#af000000",
						textColor: "#ffffff",
						groupId: "clipboard",
						maxLines: 1,
						align: "center",
						isGlobal: true,
						useCloseButton: true,
						autoHideTimeout: 1500,
						hideOnTap: true
					}, "copy")).show();
				}
				else
				{
					BX.UI.Notification.Center.notify({
						content: BX.message('IM_COPIED'),
						autoHideDelay: 2000
					});
					BX.MessengerCommon.clipboardCopy(button.ACTION_VALUE);
				}
			}
			else if (button.ACTION === 'DIALOG')
			{
				this.BXIM.openMessenger(button.ACTION_VALUE);
			}
		}

		return false;
	}

	MessengerCommon.prototype.clickButtonKeyboard = function()
	{
		if (BX.proxy_context.tagName == 'A')
			return true;

		if (this.sendBotCommand)
			return true;

		var dialogId = BX.proxy_context.getAttribute('data-dialogId');
		var messageId = BX.proxy_context.getAttribute('data-messageId');
		var botId = BX.proxy_context.getAttribute('data-botId');
		var command = BX.proxy_context.getAttribute('data-command');
		var commandParams = BX.proxy_context.getAttribute('data-commandParams');
		var disabled = BX.proxy_context.getAttribute('data-disabled');
		var blockAfterClick = BX.proxy_context.getAttribute('data-blockAfterClick');

		if (disabled == 'Y' || BX.hasClass(BX.proxy_context, 'bx-messenger-keyboard-button-block'))
			return true;

		this.sendBotCommand = true;
		if (!this.sendBotCommandBlock[botId])
		{
			this.sendBotCommandBlock[botId] = {};
		}
		this.sendBotCommandBlock[botId][messageId] = true;

		if (blockAfterClick == 'Y')
		{
			var messageKeyboardBox = BX('im-message-keyboard-'+messageId);
			if (messageKeyboardBox)
			{
				var nodesButton = BX.findChildrenByClassName(messageKeyboardBox, "bx-messenger-keyboard-button-text", false);
				for (var i = 0; i < nodesButton.length; i++)
				{
					BX.addClass(nodesButton[i], "bx-messenger-keyboard-button-block");
				}
			}
		}
		BX.addClass(BX.proxy_context, 'bx-messenger-keyboard-button-progress bx-messenger-keyboard-button-block');

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?BOT_COMMAND&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_BOT_COMMAND' : 'Y', 'BOT_ID': botId, 'COMMAND' : command, 'COMMAND_PARAMS' : commandParams, 'DIALOG_ID': dialogId, 'MESSAGE_ID': messageId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				this.sendBotCommand = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.sendBotCommand = false;
			}, this)
		});

		return true;
	}

	/* Section: Attach */
	MessengerCommon.prototype.drawAttach = function(messageId, chatId, attachConfig, params)
	{
		if (!attachConfig || attachConfig.length == 0)
			return [];

		var attachArray = [];
		if (typeof(attachConfig) != 'object')
		{
			attachArray.push(attachConfig);
		}
		else
		{
			attachArray = attachConfig;
		}
		params = params || {};

		var userColor = this.getUserIdByChatId(chatId);

		var nodeCollection = [];
		for (var j = 0; j < attachArray.length; j++)
		{
			var attachBlock = attachArray[j];
			if (!attachBlock) continue;

			var color = "";
			if (typeof(attachBlock.COLOR) != 'undefined')
			{
				color = attachBlock.COLOR;
			}
			else if (userColor && this.BXIM.messenger.users[userColor])
			{
				color = this.BXIM.messenger.users[userColor].color;
			}
			else if (this.BXIM.messenger.chat[chatId])
			{
				color = this.BXIM.messenger.chat[chatId].color;
			}
			else if (this.BXIM.messenger.users[this.BXIM.userId])
			{
				color = this.BXIM.messenger.users[this.BXIM.userId].color;
			}

			if (typeof(attachBlock['BLOCKS']) != 'object')
			{
				continue;
			}

			var attachId = typeof(attachBlock['ID']) != 'undefined'? attachBlock['ID']: 0;

			var blockCollection = [];

			var deleteAttachId = false;
			if (
				attachId &&
				this.BXIM.messenger.message[messageId] &&
				this.BXIM.messenger.message[messageId].params &&
				this.BXIM.messenger.message[messageId].params.URL_ID &&
				(
					this.BXIM.messenger.message[messageId].params.URL_ID.indexOf(attachId) > -1
					|| this.BXIM.messenger.message[messageId].params.URL_ID.indexOf(parseInt(attachId)) > -1
				)
			)
			{
				if (!this.BXIM.settings.enableRichLink)
				{
					continue;
				}

				if (this.BXIM.messenger.message[messageId].senderId == this.BXIM.userId)
				{
					deleteAttachId = true;
				}
			}

			if (deleteAttachId)
			{
				blockCollection.push(
					BX.create("span", { props : { className: "bx-messenger-attach-delete"}, attrs: {'data-attachId': attachId, 'data-messageId': messageId, 'data-action': 'url'}})
				);
			}

			for (var k = 0; k < attachBlock['BLOCKS'].length; k++)
			{
				var attach = attachBlock['BLOCKS'][k];
				var blockNode = null;
				if (attach.USER && attach.USER.length > 0)
				{
					var userNodes = [];
					for (var i = 0; i < attach.USER.length; i++)
					{
						var linkTitle = null;
						if (attach.USER[i].NETWORK_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'network', 'data-networkId': attach.USER[i].NETWORK_ID}, text: attach.USER[i].NAME});
						}
						else if (attach.USER[i].BOT_ID)
						{
							if (this.BXIM.messenger.users[attach.USER[i].BOT_ID])
							{
								attach.USER[i].NAME = this.BXIM.messenger.users[attach.USER[i].BOT_ID].name;
								attach.USER[i].AVATAR = this.BXIM.messenger.users[attach.USER[i].BOT_ID].avatar;
							}
							else if (!this.BXIM.messenger.bot[attach.USER[i].BOT_ID])
							{
								attach.USER[i].AVATAR = '';
							}

							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'user', 'data-userId': attach.USER[i].BOT_ID}, text: attach.USER[i].NAME});
						}
						else if (attach.USER[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax "+(attach.USER[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-userId': attach.USER[i].USER_ID}, text: attach.USER[i].NAME});
							if (this.BXIM.messenger.users[attach.USER[i].USER_ID])
							{
								attach.USER[i].AVATAR = this.BXIM.messenger.users[attach.USER[i].USER_ID].avatar;
							}
						}
						else if (attach.USER[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.USER[i].CHAT_ID}, text: attach.USER[i].NAME});
						}
						else if (attach.USER[i].LINK)
						{
							linkTitle = BX.create("a", {attrs: {'href': this.formatUrl(attach.USER[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-attach-user-name"}, text: attach.USER[i].NAME});
						}
						else
						{
							linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-user-name"}, text: attach.USER[i].NAME})
						}

						var avatarType = 'user';
						if (attach.USER[i].AVATAR_TYPE == 'CHAT')
						{
							avatarType = 'chat';
						}
						else if (attach.USER[i].AVATAR_TYPE == 'BOT')
						{
							avatarType = 'bot';
						}

						var userNode = BX.create("span", { props : { className: "bx-messenger-attach-user"}, children: [
							BX.create("span", { props : { className: "bx-messenger-attach-user-avatar"}, children: [
								attach.USER[i].AVATAR?
									BX.create("img", { attrs:{'src': this.formatUrl(attach.USER[i].AVATAR)}, props : { className: "bx-messenger-attach-user-avatar-img"}}):
									BX.create("span", { attrs: {style: "background-color: "+color}, props : { className: "bx-messenger-attach-user-avatar-img bx-messenger-attach-"+avatarType+"-avatar-default "}})
							]}),
							linkTitle
						]});
						userNodes.push(userNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-users"}, children: userNodes});
				}
				else if (attach.LINK && attach.LINK.length > 0)
				{
					var linkNodes = [];
					for (var i = 0; i < attach.LINK.length; i++)
					{
						var linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-link-name"}, text: attach.LINK[i].NAME? attach.LINK[i].NAME: attach.LINK[i].LINK});
						if (attach.LINK[i].NETWORK_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "}, attrs: {'data-entity': 'network', 'data-networkId': attach.LINK[i].NETWORK_ID}, children: [linkTitle]});
						}
						else if (attach.LINK[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "+(attach.LINK[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-userId': attach.LINK[i].USER_ID}, children: [linkTitle]});
						}
						else if (attach.LINK[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.LINK[i].CHAT_ID}, children: [linkTitle]});
						}
						else
						{
							linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-link-name"}, children: [
								BX.create("a", {attrs: {'href': this.formatUrl(attach.LINK[i].LINK), 'target': '_blank'}, text: attach.LINK[i].NAME? attach.LINK[i].NAME: attach.LINK[i].LINK})
							]});
						}

						var linkDesc = null;
						if (attach.LINK[i].HTML)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-link-desc"}, html: BX.MessengerCommon.prepareText(attach.LINK[i].HTML, true, true, true)});
						}
						else if (attach.LINK[i].DESC)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-link-desc"}, html: BX.MessengerCommon.prepareText(attach.LINK[i].DESC, true, true, true)});
						}

						var linkPreview = null;
						if (attach.LINK[i].PREVIEW)
						{
							linkPreview = BX.create("span", { props : { className: "bx-messenger-file-image-src"}, children: [
								BX.create("img", { attrs:{'src': this.formatUrl(attach.LINK[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this, true)"}, props : { className: "bx-messenger-file-image-text"}}),
							]});
							var link = BX.create("div", {children: [
								linkTitle,
								linkDesc,
								linkPreview
							]});
						}
						else
						{
							var link = BX.create("div", {children: [
								linkTitle,
								linkDesc
							]});
						}
						linkNodes.push(link);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-links"}, children: linkNodes});
				}
				else if (attach.RICH_LINK && attach.RICH_LINK.length > 0)
				{
					var linkNodes = [];
					for (var i = 0; i < attach.RICH_LINK.length; i++)
					{
						var convert = document.createElement("p");
						if (attach.RICH_LINK[i].NAME)
						{
							convert.innerHTML = attach.RICH_LINK[i].NAME;
							attach.RICH_LINK[i].NAME = convert.innerText;
						}
						if (attach.RICH_LINK[i].DESC)
						{
							convert.innerHTML = attach.RICH_LINK[i].DESC;
							attach.RICH_LINK[i].DESC = convert.innerText;
						}

						var linkSource = null;
						var linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-rich-link-name"}, text: attach.RICH_LINK[i].NAME? attach.RICH_LINK[i].NAME: attach.RICH_LINK[i].LINK});
						if (attach.RICH_LINK[i].NETWORK_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "}, attrs: {'data-entity': 'network', 'data-networkId': attach.RICH_LINK[i].NETWORK_ID}, children: [linkTitle]});
						}
						else if (attach.RICH_LINK[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "+(attach.RICH_LINK[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-userId': attach.RICH_LINK[i].USER_ID}, children: [linkTitle]});
						}
						else if (attach.RICH_LINK[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.RICH_LINK[i].CHAT_ID}, children: [linkTitle]});
						}
						else
						{
							if (attach.RICH_LINK[i].HTML)
							{
								linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-rich-link-name"}, children: [
									BX.create("a", {attrs: {'href': attach.RICH_LINK[i].LINK, 'target': '_blank'}, text: attach.RICH_LINK[i].NAME? attach.RICH_LINK[i].NAME: attach.RICH_LINK[i].LINK})
								]});
							}
							linkSource = BX.create("div", { props : { className: "bx-messenger-attach-rich-link-source"}, html: BX.create("a", {attrs: {'href': attach.RICH_LINK[i].LINK}}).hostname});
						}

						var linkDesc = null;
						if (attach.RICH_LINK[i].DESC)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-rich-link-desc"}, text: attach.RICH_LINK[i].DESC});
						}

						var linkPreview = null;
						if (attach.RICH_LINK[i].HTML)
						{
							linkPreview = BX.create("div", { props : { className: "bx-messenger-attach-rich-link-html"}, text: attach.RICH_LINK[i].HTML});
							var link = BX.create("span", {props : { className: "bx-messenger-attach-rich-link"+(attach.RICH_LINK[i].PREVIEW? " bx-messenger-attach-rich-link-with-preview": "")}, children: [linkTitle, linkDesc, linkPreview]})
						}
						else if (attach.RICH_LINK[i].PREVIEW)
						{
							linkPreview = BX.create("span", { props : { className: "bx-messenger-file-image-src"}, children: [
								BX.create("img", { attrs:{'src': this.formatUrl(attach.RICH_LINK[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this, true)"}, props : { className: "bx-messenger-file-image-text"}}),
							]});
							var link = BX.create("a", {attrs: {'href': attach.RICH_LINK[i].LINK, 'target': '_blank'}, props : { className: "bx-messenger-file-image"}, children: [
								linkPreview,
								BX.create("span", {props : { className: "bx-messenger-attach-rich-link-panel"}, children: [linkTitle, linkDesc, linkSource]})
							]});
						}
						else
						{
							var link = BX.create("a", {attrs: {'href': attach.RICH_LINK[i].LINK, 'target': '_blank'}, props : { className: "bx-messenger-file-image bx-messenger-file-image-without-preview"}, children: [
								BX.create("span", {props : { className: "bx-messenger-attach-rich-link-panel"}, children: [linkTitle, linkDesc, linkSource]})
							]});
						}
						linkNodes.push(link);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-rich-links"}, children: linkNodes});
				}
				else if(attach.MESSAGE && attach.MESSAGE.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: BX.MessengerCommon.prepareText(attach.MESSAGE, true, true, true)});
				}
				else if(attach.HTML && attach.HTML.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: BX.MessengerCommon.prepareText(attach.HTML, true, true, true)});
				}
				else if(attach.GRID && attach.GRID.length > 0)
				{
					var gridNodes = [];
					for (var i = 0; i < attach.GRID.length; i++)
					{
						var gridValue = BX.MessengerCommon.prepareText(attach.GRID[i].VALUE, true, true, true);
						if (attach.GRID[i].USER_ID)
						{
							gridValue = '<span class="bx-messenger-ajax '+(attach.GRID[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')+'" data-entity="user" data-userId="'+attach.GRID[i].USER_ID+'">'+gridValue+'</span>';
						}
						else if (attach.GRID[i].CHAT_ID)
						{
							gridValue = '<span class="bx-messenger-ajax" data-entity="chat" data-chatId="'+attach.GRID[i].CHAT_ID+'">'+gridValue+'</span>';
						}
						else if (attach.GRID[i].LINK)
						{
							gridValue = '<a href="'+this.formatUrl(attach.GRID[i].LINK)+'" target="_blank">'+gridValue+'</a>';
						}
						var width = attach.GRID[i].WIDTH? 'width: '+attach.GRID[i].WIDTH+'px': '';
						var height = attach.GRID[i].HEIGHT? 'max-height: '+attach.GRID[i].HEIGHT+'px;': '';
						var maxHeight = 0;

						var gridNode = null;
						var gridValueTest = null;
						if (height)
						{
							gridValueTest = BX.create("div", { props : { className: "bx-messenger-attach bx-messenger-attach-block-name"}, attrs: { style: "position: absolute; left: -1000px;"+(attach.GRID[i].DISPLAY == 'ROW'? width: '')}, html: gridValue});
							document.body.appendChild(gridValueTest);
							if (attach.GRID[i].HEIGHT >= gridValueTest.offsetHeight)
							{
								height = '';
							}
							else
							{
								maxHeight = gridValueTest.offsetHeight;
							}
							BX.remove(gridValueTest);
						}
						if (height)
						{
							gridNode = BX.create("span", { props : { className: "bx-messenger-attach-block bx-messenger-attach-block-"+(attach.GRID[i].DISPLAY.toLowerCase())+" bx-messenger-attach-block-spoiler"}, attrs: { style: attach.GRID[i].DISPLAY == 'LINE' || attach.GRID[i].DISPLAY == 'CARD'? width: ''}, children: [
								BX.create("div", { props : { className: "bx-messenger-attach-block-name"}, attrs: { style: attach.GRID[i].DISPLAY == 'ROW'? width: ''}, children: [
									BX.create("span", {props : { className: "bx-messenger-attach-block-spoiler-name"}, text: attach.GRID[i].NAME}),
									BX.create("span", {props : { className: "bx-messenger-attach-block-spoiler-icon"}})
								]}),
								BX.create("div", { props : { className: "bx-messenger-attach-block-value"}, attrs: { style: height+(attach.GRID[i].COLOR? 'color: '+attach.GRID[i].COLOR: ''), 'data-min-height': attach.GRID[i].HEIGHT, 'data-max-height': maxHeight}, children: [
									BX.create("span", {html: gridValue})
								]})
							]});
						}
						else
						{
							var blockType = attach.GRID[i].DISPLAY;
							if (
								(blockType == 'row' || blockType == 'column')
								&& (!attach.GRID[i].NAME || !attach.GRID[i].VALUE)
							)
							{
								blockType = 'BLOCK';
							}
							gridNode = BX.create("span", { props : { className: "bx-messenger-attach-block bx-messenger-attach-block-"+blockType.toLowerCase()}, attrs: { style: blockType == 'LINE' || blockType == 'CARD'? width: ''}, children: [
								!attach.GRID[i].NAME? null: BX.create("div", { props : { className: "bx-messenger-attach-block-name"}, attrs: { style: blockType == 'ROW'? width: ''}, text: attach.GRID[i].NAME}),
								!attach.GRID[i].VALUE? null: BX.create("div", { props : { className: "bx-messenger-attach-block-value"}, attrs: { style: (attach.GRID[i].COLOR? 'color: '+attach.GRID[i].COLOR: '')}, html: gridValue})
							]});
						}
						gridNodes.push(gridNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-blocks"}, children: gridNodes});
				}
				else if (attach.DELIMITER)
				{
					var attrs = "";
					if (attach.DELIMITER.SIZE)
					{
						attrs += "width: "+attach.DELIMITER.SIZE+"px;"
					}
					if (attach.DELIMITER.COLOR)
					{
						attrs += "background-color: "+attach.DELIMITER.COLOR
					}
					if (attrs)
					{
						attrs = {style: attrs};
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-delimiter"}, attrs: attrs});
				}
				else if (attach.IMAGE && attach.IMAGE.length > 0)
				{
					var imageNodes = [];
					for (var i = 0; i < attach.IMAGE.length; i++)
					{
						if (!attach.IMAGE[i].NAME)
						{
							attach.IMAGE[i].NAME = "";
						}

						if (!attach.IMAGE[i].PREVIEW)
						{
							attach.IMAGE[i].PREVIEW = attach.IMAGE[i].LINK;
						}
						var imageNode = BX.create("a", { props : { className: "bx-messenger-file-image-src"}, attrs: {'href': attach.IMAGE[i].LINK, 'target': '_blank', 'title': attach.IMAGE[i].NAME}, children: [
							BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.IMAGE[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this)"}, props : { className: "bx-messenger-attach-image bx-messenger-file-image-link"}})
						]})

						imageNodes.push(imageNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-images"}, children: imageNodes});
				}
				else if(attach.FILE && attach.FILE.length > 0)
				{
					var filesNodes = [];
					for (var i = 0; i < attach.FILE.length; i++)
					{
						var fileName = attach.FILE[i].NAME? attach.FILE[i].NAME: attach.FILE[i].LINK;
						if (this.isMobile())
						{
							if (fileName.length > 20)
							{
								fileName = fileName.substr(0, 7)+'...'+fileName.substr(fileName.length-10, fileName.length);
							}
						}
						else
						{
							if (fileName.length > 43)
							{
								fileName = fileName.substr(0, 20)+'...'+fileName.substr(fileName.length-20, fileName.length);
							}
						}
						fileName = BX.create("span", { attrs: {'title': attach.FILE[i].NAME}, props : { className: "bx-messenger-file-title"}, children: [
							BX.create("span", { props : { className: "bx-messenger-file-title-name"}, text: fileName})
						]});
						var fileNode = BX.create("div", { props : { className: "bx-messenger-file"}, children: [
							BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
								BX.create("a", { props : { className: "bx-messenger-file-title-href"}, attrs: {'href': BX.util.htmlspecialcharsback(attach.FILE[i].LINK), 'target': '_blank'}, children: [fileName]}),
								attach.FILE[i].SIZE? BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(attach.FILE[i].SIZE)}): null
							]}),
							BX.create("div", { props : { className: "bx-messenger-file-download"}, children: [
								BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.FILE[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"}, html: BX.message('IM_F_DOWNLOAD')})
							]})
						]});
						filesNodes.push(fileNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-files"}, children: filesNodes});
				}
				blockCollection.push(blockNode);
			}

			if (blockCollection.length > 0)
			{
				nodeCollection.push(BX.create("div", {
					props : { className: "bx-messenger-attach"},
					attrs: { 'style': color == 'transparent'? 'border: 0; padding-left: 0;': 'border-color: '+color},
					children: blockCollection
				}));
			}
		}
		return nodeCollection
	}



	/* Section: Disk Manager */
	MessengerCommon.prototype.diskGetMessageId = function(chatId, fileId)
	{
		for (var messageId in this.BXIM.messenger.message)
		{
			if (!this.BXIM.messenger.message.hasOwnProperty(messageId))
			{
				continue;
			}

			var message = this.BXIM.messenger.message[messageId];
			if (message.params['FILE_ID'] && message.params['FILE_ID'].length > 0)
			{
				var result = message.params['FILE_ID'].find(function(element) {
					return element == fileId;
				});
				if (result)
				{
					return message.id;
				}
			}
		}

		return 0;
	}

	MessengerCommon.prototype.diskDrawFiles = function(chatId, fileId, params)
	{
		if (!this.BXIM.disk.enable || !chatId || !fileId)
			return [];

		var fileIds = [];
		if (typeof(fileId) != 'object')
		{
			fileIds.push(fileId);
		}
		else
		{
			fileIds = fileId;
		}
		params = params || {};

		var enableLink = true;
		var nodeCollection = []

		for (var i = 0; i < fileIds.length; i++)
		{
			var file = this.BXIM.disk.files[chatId] && this.BXIM.disk.files[chatId][fileIds[i]];

			if (!file)
			{
				var file = {'id': fileIds[i], 'chatId': chatId};
				var boxId = params.boxId? params.boxId: 'im-file';

				nodeCollection.push(BX.create("div", {
					attrs: { id: boxId+'-'+file.id, 'data-chatId': file.chatId , 'data-fileId': file.id, 'data-boxId': boxId},
					props : { className: "bx-messenger-file"},
					children: [BX.create("span", { props : { className: "bx-messenger-file-deleted"}, html: BX.message('IM_F_DELETED')})]
				}));

				continue;
			}

			if (this.isDesktop())
			{
				if (!this.BXIM.desktop.enableInVersion(43))
				{
					if (file.type == 'audio')
					{
						file.viewerAttrs = null;
					}
				}
				if (!this.BXIM.desktop.enableInVersion(47))
				{
					if (file.type == 'video')
					{
						file.viewerAttrs = null;
					}
				}
			}

			if (params.status)
			{
				if (typeof(params.status) != 'object')
				{
					params.status = [params.status];
				}
				if (!BX.util.in_array(file.status, params.status))
				{
					continue;
				}
			}

			var preview = null;
			var datasetSetted = false;
			if (file.preview || file.urlPreview)
			{
				var imageNode = null;
				if (file.preview && typeof(file.preview) != 'string')
				{
					imageNode = file.preview;
					if (file.urlPreview)
					{
						file.preview = '';
					}
				}
				else
				{
					let fileUrl = this.formatUrl(file.urlPreview? file.urlPreview: file.preview);
					if (this.isMobile() && file.type === 'image')
					{
						fileUrl = this.toBXUrl(fileUrl);
					}

					imageNode = BX.create("img", {
						attrs:{
							'src': fileUrl,
							'height': file.image? (file.image.height > 400? '400': file.image.height): 'auto'
						},
						props : { className: "bx-messenger-file-image-text bx-messenger-file-image-type-"+file.type},
						events: { load: function(){
							this.parentNode.style.background = "#fff";
							this.removeAttribute('height');
						}}
					});
				}

				if (enableLink)
				{
					var videoPlayNode = null;
					if (file.type == 'video')
					{
						if (this.isMobile())
						{
							videoPlayNode = BX.create("div", {props : { className: "bx-messenger-file-image-type-video-button"},  children: [
								BX.create("div", {events: {click: BX.delegate(function(e){
									BX.localStorage.set('impmh', true, 1);
									app.openDocument({url: this.formatUrl(file.urlDownload), filename: file.name.toString().toLowerCase()});
									return BX.PreventDefault(e);
								}, this)}, props : { className: "bx-messenger-file-image-type-video-button-play"}})
							]});
						}
						else
						{
							videoPlayNode = BX.create("div", {props : { className: "bx-messenger-file-image-type-video-button"},  children: [
								BX.create("div", {props : { className: "bx-messenger-file-image-type-video-button-play"}}),
							]});
						}
					}

					if (
						file.type == 'video' && file.urlDownload
						|| file.type != 'video' && file.urlPreview && file.urlShow
					)
					{
						if (this.isMobile())
						{
							preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
								BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
									BX.create("span", {events: {click: BX.delegate(function(){
										var file = this.BXIM.disk.files[BX.proxy_context.dataset.chatid][BX.proxy_context.dataset.diskid];
										var res = BX.findParent(BX.proxy_context, {"className" : "bx-messenger-content-item"});
										if (res && res.getAttribute('data-messageid').indexOf('temp') == 0)
										{
											return false;
										}
										if (file.type == 'image')
										{
											this.BXIM.messenger.openPhotoGallery(file.urlShow);
											BX.localStorage.set('impmh', true, 1);
										}
										else
										{
											BX.localStorage.set('impmh', true, 1);
											app.openDocument({url: file.urlShow, filename: file.name.toString().toLowerCase()})
										}
									}, this)}, attrs: {'data-chatId': file.chatId, 'data-diskId': file.id }, props : { className: "bx-messenger-file-image-src"},  children: [
										videoPlayNode,
										imageNode
									]})
								]})
							]});
						}
						else
						{
							preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
								BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
									BX.create("a", {
										dataset: file.viewerAttrs,
										attrs: {'href': this.formatUrl(file.urlShow), 'target': '_blank'},
										props : { className: "bx-messenger-file-image-src"},
										children: [
											videoPlayNode,
											imageNode
										]
									})
								]}),
							]});
							datasetSetted = true;
						}
					}
					else
					{
						preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
								BX.create("span", {props : { className: "bx-messenger-file-image-src"},  children: [
									imageNode
								]})
							]}),
						]});
					}
				}
				else
				{
					preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
						BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image-src"},  children: [
								imageNode
							]})
						]}),
					]});
				}
			}
			var fileName = file.name;
			if (this.isMobile())
			{
				if (fileName.length > 20)
				{
					fileName = fileName.substr(0, 7)+'...'+fileName.substr(fileName.length-10, fileName.length);
				}
			}
			else
			{
				if (fileName.length > 43)
				{
					fileName = fileName.substr(0, 20)+'...'+fileName.substr(fileName.length-20, fileName.length);
				}
			}

			if (file.type === 'audio' && (file.viewerAttrs || this.isMobile()))
			{
				title = BX.create("div", { props : { className: "bx-messenger-audioplayer-container bx-messenger-audioplayer-container-dark"}, children: [
					BX.create("div", { props : { className: "bx-messenger-audioplayer-controls-container"}, children: [
						BX.create("div", { props : { className: "bx-messenger-audioplayer-control bx-messenger-audioplayer-control-play"}})
					]}),
					BX.create("div", { props : { className: "bx-messenger-audioplayer-timeline-container"}, children: [
						BX.create("div", { props : { className: "bx-messenger-audioplayer-track-mask"}}),
						BX.create("div", { props : { className: "bx-messenger-audioplayer-track"}}),
					]})
				], events: !this.isMobile()? null: {
					click: function(){ BX.localStorage.set('impmh', true, 1);  app.openDocument({url: file.urlDownload, filename: file.name.toString().toLowerCase()}) }
				}, dataset: file.viewerAttrs});
			}
			else
			{
				var title = BX.create("span", {
					attrs: {'title': file.name}, props: {className: "bx-messenger-file-title"}, children: [
						BX.create("span", {props: {className: "bx-messenger-file-title-name"}, html: fileName})
					]
				});
				if (enableLink && (file.urlShow || file.urlDownload))
				{
					if (this.isMobile())
					{
						title = BX.create("span", { props : { className: "bx-messenger-file-title-href"}, events: {click: function(){
							BX.localStorage.set('impmh', true, 1);
							app.openDocument({url: this.urlDownload, filename: this.name.toString().toLowerCase()})
						}.bind(file)}, children: [title]});
					}
					else if (!file.viewerAttrs && BX.desktopUtils.canDownload())
					{
						title = BX.create("span", { props : { className: "bx-messenger-file-title-href"}, events: {click: function(){
							BX.desktopUtils.downloadFile(this.urlDownload, this.name);
						}.bind(file)}, children: [title]});
					}
					else
					{
						title = BX.create("a", {
							dataset: datasetSetted? null: file.viewerAttrs,
							props : { className: "bx-messenger-file-title-href"},
							attrs: {'href': this.formatUrl(file.urlShow? file.urlShow: file.urlDownload), 'target': '_blank'},
							children: [title]
						});
					}
				}
				title = BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
					title,
					file.size? BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(file.size)}): null,
				]});
			}

			var status = null;
			if (file.status == 'done')
			{
				if (!this.isMobile())
				{
					var link = null;
					if (file.urlDownload && enableLink)
					{
						if (BX.desktopUtils.canDownload())
						{
							link = BX.create("span", {
								events: {click: function(){
									BX.desktopUtils.downloadFile(this.urlDownload, this.name);
								}.bind(file)},
								props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"},
								html: BX.message('IM_F_DOWNLOAD')
							});
						}
						else
						{
							link = BX.create("a", {
								attrs: {'href': this.formatUrl(file.urlDownload), 'target': '_blank'},
								props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"},
								html: BX.message('IM_F_DOWNLOAD')
							});
						}
					}

					status = BX.create("div", { props : { className: "bx-messenger-file-download"}, children: [
						link,
						!file.urlDownload || !this.BXIM.disk.enable || this.BXIM.context == "LINES"? null: BX.create("span", { props : { className: "bx-messenger-file-download-link bx-messenger-file-download-disk"}, html: BX.message('IM_F_DOWNLOAD_DISK'), events: {click:BX.delegate(function(){
							var chatId = BX.proxy_context.parentNode.parentNode.getAttribute('data-chatId');
							var fileId = BX.proxy_context.parentNode.parentNode.getAttribute('data-fileId');
							var boxId = BX.proxy_context.parentNode.parentNode.getAttribute('data-boxId');
							this.BXIM.disk.saveToDisk(chatId, fileId, {boxId: boxId});
						}, this)}})
					]});
				}
				else
				{
					status = BX.create("div", { props : { className: "bx-messenger-file-download"}, children: []});
				}
			}
			else if (file.status == 'upload')
			{
				var statusStyles = {};
				var styles2 = '';
				var statusDelete = null;
				var statusClassName = '';
				var statusTitle = '';
				if (file.authorId == this.BXIM.userId && file.progress >= 0)
				{
					statusTitle = BX.message('IM_F_UPLOAD_2').replace('#PERCENT#', file.progress);
					statusStyles = { width: file.progress+'%' };
					statusDelete = BX.create("span", { attrs: {title: BX.message('IM_F_CANCEL')}, props : { className: "bx-messenger-file-delete"}})
				}
				else
				{
					statusTitle = BX.message('IM_F_UPLOAD');
					statusClassName = " bx-messenger-file-progress-infinite";
				}
				status = BX.create("div", { props : { className: "bx-messenger-progress-box"}, children: [
					BX.create("span", { attrs: {title: statusTitle}, props : { className: "bx-messenger-file-progress"}, children: [
						BX.create("span", { props : { className: "bx-messenger-file-progress-line"+statusClassName}, style : statusStyles})
					]}),
					statusDelete
				]});
			}
			else if (file.status == 'error')
			{
				status = BX.create("span", { props : { className: "bx-messenger-file-status-error"}, html: file.errorText? file.errorText: BX.message('IM_F_ERROR')})
			}

			if (!status)
				return false;

			if (fileIds.length == 1 && params.showInner == 'Y')
			{
				nodeCollection = [preview, title, status];
			}
			else
			{
				var boxId = params.boxId? params.boxId: 'im-file';
				nodeCollection.push(BX.create("div", {
					attrs: { id: boxId+'-'+file.id, 'data-chatId': file.chatId , 'data-fileId': file.id, 'data-boxId': boxId},
					props : { className: "bx-messenger-file"},
					children: [preview, title, status]
				}));
			}
		}

		return nodeCollection
	}

	MessengerCommon.prototype.diskRedrawFile = function(chatId, fileId, params)
	{
		params = params || {};
		var boxId = params.boxId? params.boxId: 'im-file';

		var fileBox = BX(boxId+'-'+fileId);
		if (fileBox)
		{
			var result = this.diskDrawFiles(chatId, fileId, {'showInner': 'Y', 'boxId': boxId});
			if (result)
			{
				if (
					this.BXIM.disk.files[chatId]
					&& this.BXIM.disk.files[chatId][fileId]
					&& this.BXIM.disk.files[chatId][fileId].id != fileId
				)
				{
					var newFileId = this.BXIM.disk.files[chatId][fileId].id;
					this.BXIM.disk.files[chatId][newFileId] = this.BXIM.disk.files[chatId][fileId];

					fileBox.setAttribute('data-fileid', newFileId);
					fileBox.setAttribute('id', 'im-file-' + newFileId);
				}
				fileBox.innerHTML = '';
				BX.adjust(fileBox, {children: result});
			}
		}
	}

	MessengerCommon.prototype.diskChatDialogFileInited = function(id, file, agent)
	{
		agent.messageText = agent.messageText || '';

		var chatId = agent.form.CHAT_ID.value;

		if (!this.BXIM.disk.files[chatId])
			this.BXIM.disk.files[chatId] = {};

		this.BXIM.disk.files[chatId][id] = {
			'id': id,
			'templateId': id,
			'chatId': chatId,
			'date': new Date(),
			'type': file.isImage? 'image': 'file',
			'preview': file.isImage? file.canvas: '',
			'name': BX.util.htmlspecialchars(file.name),
			'size': file.file.size,
			'status': 'upload',
			'progress': -1,
			'authorId': this.BXIM.userId,
			'authorName': this.BXIM.messenger.users[this.BXIM.userId].name,
			'urlPreview': '',
			'urlShow': '',
			'urlDownload': ''
		};

		var recipientId = 0;
		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type != 'private')
		{
			recipientId = 'chat'+chatId;
		}
		else
		{
			for (var userId in this.BXIM.messenger.userChat)
			{
				if (this.BXIM.messenger.userChat[userId] == chatId)
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

		// TODO: check
		var paramsFileId = [this.BXIM.disk.files[chatId][id].id];
		var fileType = 'file';
		var tmpMessageId = 'tempFile'+this.BXIM.disk.fileTmpId + new Date().getTime();
		this.BXIM.messenger.message[tmpMessageId] = {
			'id': tmpMessageId,
			'chatId': chatId,
			'senderId': this.BXIM.userId,
			'recipientId': recipientId,
			'date': new Date(),
			'text': BX.MessengerCommon.prepareText(agent.messageText, true, true, true),
			'textOriginal': agent.messageText,
			'params': {'FILE_ID': paramsFileId, 'CLASS': olSilentMode == "Y"? "bx-messenger-content-item-system": ""}
		};
		if (!this.BXIM.messenger.showMessage[recipientId])
			this.BXIM.messenger.showMessage[recipientId] = [];

		this.BXIM.messenger.showMessage[recipientId] = this.BXIM.messenger.showMessage[recipientId].filter(function(element) {
			return element != tmpMessageId;
		});
		this.BXIM.messenger.showMessage[recipientId].push(tmpMessageId.toString());

		BX.MessengerCommon.drawMessage(recipientId, this.BXIM.messenger.message[tmpMessageId]);
		BX.MessengerCommon.drawProgessMessage(tmpMessageId);

		this.recentListAddItem({
			id: recipientId,
			message: {
				id: tmpMessageId,
				date: new Date(),
				author_id: this.BXIM.userId,
				status: 'delivered',
				text: agent.messageText? agent.messageText: '',
				attach: false,
				file: true,
			},
		});
		this.recentListRedraw();

		this.BXIM.messenger.popupMessengerFileFormRegChatId.value = chatId;
		file.regTmpMessageId = this.BXIM.messenger.popupMessengerFileFormRegMessageId.value = tmpMessageId;
		file.regHiddenMessageId = this.BXIM.messenger.popupMessengerFileFormRegMessageHidden.value = olSilentMode;
		file.regParams = this.BXIM.messenger.popupMessengerFileFormRegParams.value = JSON.stringify({
			'FILE_TMP_ID' : this.BXIM.disk.files[chatId][id].id,
			'TEXT' : agent.messageText
		});
		this.BXIM.disk.OldBeforeUnload = window.onbeforeunload;
		window.onbeforeunload = function()
		{
			return BX.message('IM_F_EFP')
		};

		this.BXIM.disk.fileTmpId++;

		agent.messageText = '';
	}

	MessengerCommon.prototype.diskChatDialogFileStart = function(status, percent, agent, pIndex)
	{
		var formFields = agent.streams.packages.getItem(pIndex).data;
		var chatId = formFields.CHAT_ID;
		var fileId = this.BXIM.disk.files[chatId][status.id].id;
		if (!this.BXIM.disk.files[formFields.CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[chatId][fileId].progress = parseInt(percent);
		BX.MessengerCommon.diskRedrawFile(chatId, fileId);
	}

	MessengerCommon.prototype.diskChatDialogFileProgress = function(status, percent, agent, pIndex)
	{
		var formFields = agent.streams.packages.getItem(pIndex).data;
		var chatId = formFields.CHAT_ID;
		var fileId = this.BXIM.disk.files[chatId][status.id].id;
		if (!this.BXIM.disk.files[formFields.CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[chatId][fileId].progress = Math.max(
			parseInt(percent),
			(this.BXIM.disk.files[chatId][fileId].progress || 0));
		BX.MessengerCommon.diskRedrawFile(chatId, fileId);
	}

	MessengerCommon.prototype.diskChatDialogFileDone = function(status, file, agent, pIndex)
	{
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
	}

	MessengerCommon.prototype.diskChatDialogFileError = function(item, file, agent, pIndex)
	{
		var formFields = agent.streams.packages.getItem(pIndex).data;
		this.clearProgessMessage(formFields.REG_MESSAGE_ID);

		var chatId = formFields.CHAT_ID;
		var fileId = this.BXIM.disk.files[chatId][item.id].id;
		if (!this.BXIM.disk.files[formFields.CHAT_ID][fileId])
			return false;

		item.deleteFile();

		this.BXIM.disk.files[formFields.CHAT_ID][fileId].status = "error";
		this.BXIM.disk.files[formFields.CHAT_ID][fileId].errorText = file.error;
		BX.MessengerCommon.diskRedrawFile(formFields.CHAT_ID, fileId);

		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
	}

	MessengerCommon.prototype.diskChatDialogUploadError = function(stream, pIndex, data)
	{
		var files = stream.post.REG_PARAMS? JSON.parse(stream.post.REG_PARAMS): {};
		var messages = {};
		for (var tmpId in files)
		{
			if (this.BXIM.disk.filesMessage[tmpId])
			{
				delete this.BXIM.disk.filesMessage[tmpId];
			}
			if (this.BXIM.disk.files[stream.post.REG_CHAT_ID])
			{
				if (this.BXIM.disk.files[stream.post.REG_CHAT_ID][files[tmpId]])
				{
					this.BXIM.disk.files[stream.post.REG_CHAT_ID][files[tmpId]].status = 'error';
					BX.MessengerCommon.diskRedrawFile(stream.post.REG_CHAT_ID, files[tmpId]);
				}
				if (this.BXIM.disk.files[stream.post.REG_CHAT_ID][tmpId])
				{
					this.BXIM.disk.files[stream.post.REG_CHAT_ID][tmpId].status = 'error';
					BX.MessengerCommon.diskRedrawFile(stream.post.REG_CHAT_ID, tmpId);
				}

			}
			delete this.BXIM.disk.filesProgress[tmpId];
		}
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
		BX.MessengerCommon.drawTab(this.getRecipientByChatId(stream.post.REG_CHAT_ID));
	}

	/* Section: Telephony */

	MessengerCommon.prototype.getUser = function(userId)
	{
		return this.BXIM.messenger.users[userId] || false;
	}

	MessengerCommon.prototype.phoneGetCallFields = function(chatId)
	{
		if (!this.BXIM.messenger.chat[chatId] || this.BXIM.messenger.chat[chatId].type != "call")
		{
			return {crm: false};
		}

		var currentChat = this.BXIM.messenger.chat[chatId];

		var crmData = currentChat.entity_data_1.toString().split('|');
		if (!this.BXIM.bitrixCrm || crmData.length < 3 || crmData[0] !== 'Y' || !this.BXIM.path.crm[crmData[1]])
		{
			return {crm: false};
		}
		else
		{
			return {
				crm: true,
				crmEntityType: crmData[1],
				crmEntityId: crmData[2],
				crmShowUrl: this.BXIM.path.crm[crmData[1]].replace("#ID#", crmData[2])
			};
		}
	}

	MessengerCommon.prototype.getHrPhoto = function(userId, color)
	{
		var hrphoto = '';

		if(color === undefined)
		{
			color = this.BXIM.messenger.users[userId].color || '';
		}

		if (userId == 'phone')
		{
			hrphoto = '/bitrix/js/im/images/hidef-phone-v3.png';
		}
		else if (this.BXIM.messenger.hrphoto[userId])
		{
			hrphoto = this.BXIM.messenger.hrphoto[userId];
			if (this.BXIM.messenger.hrphoto[userId] != '/bitrix/js/im/images/hidef-avatar-v3.png')
			{
				color = '';
			}
		}
		else if (!this.BXIM.messenger.users[userId] || this.BXIM.messenger.users[userId].avatar == this.BXIM.pathToBlankImage)
		{
			hrphoto = '/bitrix/js/im/images/hidef-avatar-v3.png'
		}
		else
		{
			hrphoto = this.BXIM.messenger.users[userId].avatar;
			color = '';
		}

		return {'src': hrphoto, 'color': color};
	};

	/* OPEN LINES */
	MessengerCommon.prototype.linesBodyScroll = function()
	{
		if (this.isMobile() && document.body.offsetHeight <= window.innerHeight)
		{
			this.BXIM.messenger.popupMessengerBody.scrollTop = 0;
			return false;
		}

		if (this.BXIM.animationSupport)
		{
			if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
				this.BXIM.messenger.popupMessengerBodyAnimation.stop();

			BX.defer(function(){
				(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
					duration : 600,
					start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop },
					finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(BX.MessengerCommon.isMobile()? 0: 1)},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
					step : BX.delegate(function(state){
						this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
					}, this)
				})).animate();
			}, this)();
		}
		else
		{
			BX.defer(function(){
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(BX.MessengerCommon.isMobile()? 0: 1);
			}, this)();
		}
	}

	MessengerCommon.prototype.linesGetSessionHistory = function(sessionId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?SESSION_GET_HISTORY&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'sessionGetHistory', 'SESSION_ID': sessionId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
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
						if (!this.BXIM.messenger.disk.files[data.CHAT_ID])
							this.BXIM.messenger.disk.files[data.CHAT_ID] = {};
						if (this.BXIM.messenger.disk.files[data.CHAT_ID][i])
							continue;
						data.FILES[i].date = new Date(data.FILES[i].date);
						this.BXIM.messenger.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					this.BXIM.messenger.sendAjaxTry = 0;
					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						data.MESSAGE[i].textOriginal = data.MESSAGE[i].text;
						data.MESSAGE[i].text = BX.MessengerCommon.prepareText(data.MESSAGE[i].text, true, true, true);

						this.BXIM.messenger.message[i] = data.MESSAGE[i];
					}

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = data.USERS[i].last_activity_date? new Date(data.USERS[i].last_activity_date): false;
						data.USERS[i].mobile_last_date = data.USERS[i].mobile_last_date? new Date(data.USERS[i].mobile_last_date): false;
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}

					for (var i in data.CHAT)
					{
						if (!this.BXIM.messenger.chat[i])
						{
							data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
							this.BXIM.messenger.chat[i] = data.CHAT[i];
						}
					}

					if (data.OPENLINES.canVoteAsHead)
					{
						if (!this.BXIM.messenger.openlines.canVoteAsHead)
						{
							this.BXIM.messenger.openlines.canVoteAsHead = {};
						}

						for (var i in data.OPENLINES.canVoteAsHead)
						{
							this.BXIM.messenger.openlines.canVoteAsHead[i] = data.OPENLINES.canVoteAsHead[i];
						}
					}

					this.BXIM.messenger.linesShowHistory(data.CHAT_ID, {'HISTORY': data.USERS_MESSAGE, 'FILES': data.FILES, 'CAN_JOIN': data.CAN_JOIN, 'CAN_VOTE_HEAD': data.CAN_VOTE_HEAD, 'SESSION_VOTE_HEAD': data.SESSION_VOTE_HEAD, 'SESSION_COMMENT_HEAD': data.SESSION_COMMENT_HEAD, 'SESSION_ID': data.SESSION_ID});
				}
				else
				{
					if (data.CODE == 'ACCESS_DENIED')
					{
						this.BXIM.openConfirm(data.ERROR);
					}
					else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(function(){MessengerCommon.prototype.linesGetSessionHistory(sessionID)}, 1000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.sendAjaxTry = 0;
			}, this)
		});
	}

	MessengerCommon.prototype.linesJoinSession = function(chatId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?JOIN_SESSION&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'joinSession', 'CHAT_ID': chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	}

	MessengerCommon.prototype.linesStartSession = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?START_SESSION_BY_CHAT&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'startSession', 'CHAT_ID': chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	}

	MessengerCommon.prototype.linesStartSessionByMessage = function(messageId)
	{
		if (!this.BXIM.messenger.message[messageId])
		{
			return false;
		}

		var chatId = this.BXIM.messenger.message[messageId].chatId;

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?START_SESSION_BY_MESSAGE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'startSessionByMessage', 'CHAT_ID' : chatId, 'MESSAGE_ID' : messageId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesOpenNewDialogByMessage = function(messageId)
	{
		if (!this.BXIM.messenger.message[messageId])
		{
			return false;
		}

		var chatId = this.BXIM.messenger.message[messageId].chatId;

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?OPEN_NEW_DIALOG_BY_MESSAGE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'openNewDialogByMessage', 'CHAT_ID' : chatId, 'MESSAGE_ID' : messageId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};


	MessengerCommon.prototype.linesOpenSession = function(userCode, params)
	{
		params = params || {};

		BX.ajax({
			url: this.BXIM.pathToAjax+'?OPEN_SESSION&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'openSession', 'USER_CODE': userCode, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				if (data.ERROR == '')
				{
					this.BXIM.messenger.openMessenger('chat'+data.CHAT_ID, params).then(function() {
						if (BX.MessengerWindow && this.isLinesOperator())
						{
							if (BX.MessengerWindow.currentTab != 'im-ol')
							{
								BX.MessengerWindow.changeTab('im-ol');
							}
						}
					}.bind(this));
				}
				else
				{
					if (data.CODE == 'ACCESS_DENIED')
					{
						this.BXIM.openConfirm(data.ERROR);
					}
				}
			}, this)
		});
	}

	MessengerCommon.prototype.linesVoteDraw = function(messageId)
	{
		if (
			!this.BXIM.messenger.message[messageId]
			|| !this.BXIM.messenger.message[messageId].params
			|| !this.BXIM.messenger.message[messageId].params.IMOL_VOTE
		)
		{
			return null;
		}

		var message = this.BXIM.messenger.message[messageId];

		var disableAction = false;
		if (this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat')
		{
			var lineSource = this.linesGetSource(this.BXIM.messenger.chat[this.BXIM.messenger.message[messageId].chatId]);
			if (!lineSource)
			{
				return null;
			}
			if (
				!this.BXIM.messenger.users[this.BXIM.userId].connector &&
				!(lineSource == 'livechat' || lineSource == 'network' || lineSource == 'support24Question')
			)
			{
				return null;
			}

			disableAction =
				!this.BXIM.messenger.users[this.BXIM.userId].connector
				&& !(lineSource == 'network' || lineSource == 'support24Question');
		}
		else if (
			!this.BXIM.messenger.bot[this.BXIM.messenger.currentTab]
			|| (
				this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'network'
				&& this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'support24'
			)
		)
		{
			return null;
		}

		var blockText = '';
		var blockDone = false;
		if (message.params.IMOL_VOTE == 'like')
		{
			blockDone = true;
			blockText = message.params.IMOL_VOTE_LIKE;
		}
		else if (message.params.IMOL_VOTE == 'dislike')
		{
			blockDone = true;
			blockText = message.params.IMOL_VOTE_DISLIKE;
		}
		else
		{
			blockText = message.params.IMOL_VOTE_TEXT;
		}

		return BX.create('div', {attrs: {'data-messageId': messageId}, props : {className: 'bx-messenger-content-item-vote-block'+(blockDone? ' bx-messenger-content-item-vote-block-done': '')}, children: [
			BX.create('div', {props : {className: 'bx-messenger-content-item-vote-block-text'}, html: BX.util.htmlspecialchars(blockText)}),
			BX.create('div', {props : {className: 'bx-messenger-content-item-vote-block-buttons'}, children: [
				BX.create('span', {attrs: {title: BX.message('IM_OL_VOTE_LIKE')}, props : {className: 'bx-messenger-content-item-vote-block-like'+(disableAction? ' bx-messenger-content-item-vote-block-disabled': '')}, events: {click: disableAction? function(){}: BX.delegate(function(){ this.linesVoteSend(this.BXIM.messenger.currentTab, BX.proxy_context.parentNode.parentNode.getAttribute('data-messageId'), 'like')}, this)}}),
				BX.create('span', {attrs: {title: BX.message('IM_OL_VOTE_DISLIKE')}, props : {className: 'bx-messenger-content-item-vote-block-dislike'+(disableAction? ' bx-messenger-content-item-vote-block-disabled': '')}, events: {click: disableAction? function(){}: BX.delegate(function(){ this.linesVoteSend(this.BXIM.messenger.currentTab, BX.proxy_context.parentNode.parentNode.getAttribute('data-messageId'), 'dislike')}, this)}})
			]}),
			BX.create('div', {props : {className: 'bx-messenger-content-item-vote-block-final'}, children: [
				BX.create('span', {props : {className: message.params.IMOL_VOTE == 'dislike'? 'bx-messenger-content-item-vote-block-smile-dislike': 'bx-messenger-content-item-vote-block-smile-like'}})
			]})
		]});
	}

	MessengerCommon.prototype.linesVoteResultDraw = function(messageId, messageText)
	{
		if (
			!this.BXIM.messenger.message[messageId]
			|| !this.BXIM.messenger.message[messageId].params
			|| !this.BXIM.messenger.message[messageId].params.IMOL_VOTE_SID
		)
		{
			return messageText;
		}

		var message = this.BXIM.messenger.message[messageId];

		var userResult = '';
		if (typeof(message.params.IMOL_VOTE_USER) == 'undefined' || message.params.IMOL_VOTE_USER == 0)
		{
			userResult = BX.message('IM_OL_VOTE_WO');
		}
		else if (message.params.IMOL_VOTE_USER == 5)
		{
			userResult = '<span class="bx-smile bx-im-smile-like" title="'+BX.message('IM_MESSAGE_LIKE')+'"></span>';
		}
		else
		{
			userResult = '<span class="bx-smile bx-im-smile-dislike" title="'+BX.message('IM_MESSAGE_DISLIKE')+'"></span>';
		}

		var session = this.linesGetSession(this.BXIM.messenger.chat[message.chatId]);
		if (!session)
		{
			return messageText;
		}

		var headResult = this.linesVoteHeadNodes(message.params.IMOL_VOTE_SID, message.params.IMOL_VOTE_HEAD, session.canVoteHead);

		if(typeof message.params.IMOL_COMMENT_HEAD == 'object' && message.params.IMOL_COMMENT_HEAD)
		{
			var textCommentHead = message.params.IMOL_COMMENT_HEAD['text'];
		}
		else
		{
			var textCommentHead = message.params.IMOL_COMMENT_HEAD;
		}

		var headCommentResult = this.linesCommentHeadNodes(message.params.IMOL_VOTE_SID, textCommentHead, session.canVoteHead);

		return BX.create('div', {attrs: {'data-messageId': messageId}, children: [
			BX.create('div', {props : {className: 'bx-messenger-content-item-vote-message-text'}, html: messageText}),
			BX.create('div', {props : {className: 'bx-messenger-content-item-vote-result'}, children: [
				BX.create('div', {props: {className: "bx-messenger-content-item-vote-result-row"}, children: [
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-name"}, html: BX.message('IM_OL_VOTE_USER')+':'}),
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-value"}, html: userResult})
				]}),
				BX.create('div', {props: {className: "bx-messenger-content-item-vote-result-row"}, children: [
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-name"}, html: BX.message('IM_OL_VOTE_HEAD')+':'}),
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-value"}, children: [headResult]})
				]}),
				headCommentResult? BX.create('div', {props: {className: "bx-messenger-content-item-vote-result-row"}, children: [
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-name"}, html: BX.message('IM_OL_COMMENT_HEAD')+':'}),
					BX.create('span', {props: {className: "bx-messenger-content-item-vote-result-value"}, children: [headCommentResult]})
				]}): null
			]})
		]});
	}

	MessengerCommon.prototype.linesVoteSend = function(dialogId, messageId, rating)
	{
		if (
			!this.BXIM.messenger.message[messageId]
			|| !this.BXIM.messenger.message[messageId].params
			|| !this.BXIM.messenger.message[messageId].params.IMOL_VOTE
		)
		{
			return false;
		}

		if (
			!!this.BXIM.messenger.message[messageId].params.IMOL_DATE_CLOSE_VOTE &&
			(new Date(this.BXIM.messenger.message[messageId].params.IMOL_DATE_CLOSE_VOTE).getTime()) < (new Date().getTime())
		)
		{
			var closeVoteMessage = BX.message('IM_OL_CLOSE_VOTE_NO_DAY');
			if (
				!!this.BXIM.messenger.message[messageId].params.IMOL_TIME_LIMIT_VOTE &&
				this.BXIM.messenger.message[messageId].params.IMOL_TIME_LIMIT_VOTE > 0
			)
			{
				closeVoteMessage = BX.message('IM_OL_CLOSE_VOTE').replace('#DAYS#', BX.date.format('ddiff', (Date.now()/1000) - this.BXIM.messenger.message[messageId].params.IMOL_TIME_LIMIT_VOTE));
			}

			var container = BX.findChild(
				BX('im-message-'+messageId),
				{'class' : 'bx-messenger-content-item-vote-block-' + rating},
				true,
				false
			);

			var popupCloseVoteMessage = BX.PopupWindowManager.create('popup-close-vote-message-' + rating , container, {
				content:  BX.create('DIV', {style: {padding: '10px'}, children: closeVoteMessage}),
				zIndex: 100,
				closeIcon: {
					opacity: 1
				},
				closeByEsc: true,
				darkMode: false,
				autoHide: true,
				angle: true,
				offsetLeft: 20,
				offsetTop: 10,
				events: {
					onPopupClose: BX.proxy(function() {
						popupCloseVoteMessage.destroy();
					}, this)
				}
			})
			popupCloseVoteMessage.show();
			return false;
		}
		if (dialogId.toString().substr(0, 4) == 'chat')
		{
			var lineSource = this.linesGetSource(this.BXIM.messenger.chat[this.BXIM.messenger.message[messageId].chatId]);
			if (!lineSource)
			{
				return null;
			}
			if (
				!this.BXIM.messenger.users[this.BXIM.userId].connector &&
				!(lineSource == 'livechat' || lineSource == 'network' || lineSource == 'support24Question')
			)
			{
				return null;
			}
		}
		else if (
			!this.BXIM.messenger.bot[dialogId]
			|| (
				this.BXIM.messenger.bot[dialogId].type != 'network'
				&& this.BXIM.messenger.bot[dialogId].type != 'support24'
			)
		)
		{
			return null;
		}

		this.BXIM.messenger.message[messageId].params.IMOL_VOTE = rating;

		var messageNode = BX('im-message-'+messageId);
		if (messageNode)
		{
			messageNode.innerHTML = '';
			messageNode.appendChild(this.linesVoteDraw(messageId))
		}

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_VOTE_SEND&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_LINES_VOTE_SEND': 'Y', 'DIALOG_ID': dialogId, 'MESSAGE_ID': messageId, 'RATING': rating, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				if (this.BXIM.messenger.popupMessengerLiveChatDelayedForm)
				{
					clearTimeout(this.BXIM.messenger.popupMessengerLiveChatActionTimeout);
					this.BXIM.messenger.popupMessengerLiveChatActionTimeout = setTimeout(BX.delegate(function() {
						this.BXIM.messenger.linesLivechatFormShow(this.BXIM.messenger.popupMessengerLiveChatDelayedForm);
						this.BXIM.messenger.popupMessengerLiveChatDelayedForm = null;
					}, this), 1000);
				}
			}, this)
		});

	}

	MessengerCommon.prototype.linesSaveToQuickAnswers = function(messageId, silentMode)
	{
		if (!this.BXIM.messenger.message[messageId])
		{
			return false;
		}

		var chatId = this.BXIM.messenger.message[messageId].chatId;

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_SAVE_TO_QUICK_ANSWERS&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.message.saveToQuickAnswers',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'saveToQuickAnswers', 'CHAT_ID' : chatId, 'MESSAGE_ID' : messageId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(addResult){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				if(silentMode !== true)
				{
					if(addResult.ERROR)
					{
						this.BXIM.openConfirm(addResult.ERROR);
					}
					else
					{
						this.BXIM.openConfirm(BX.message('IM_SAVE_TO_QUICK_ANSWERS_SUCCESS'));
						this.BXIM.messenger.message[messageId].quick_saved = true;
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				if(silentMode !== true)
				{
					this.BXIM.openConfirm(BX.message('IM_SAVE_TO_QUICK_ANSWERS_ERROR'));
				}
			}, this)
		});
	};

	MessengerCommon.prototype.linesVoteHeadNodes = function(sessionId, rating, canVoteHead, bindElement)
	{
		rating = rating || 0;
		canVoteHead = canVoteHead || false;

		var ratingSelect = BX.delegate(function() {
			if (!this.BXIM.messenger.openlines.canUseVoteHead)
			{
				BX.UI.InfoHelper.show('limit_contact_center_ol_boss_rate');
				return false;
			}

			var elementRating = BX.proxy_context.getAttribute('data-rating');
			var elementSessionId = BX.proxy_context.getAttribute('data-sessionId');

			BX.proxy_context.parentNode.previousSibling.style.width = (elementRating*20)+'%';

			if (bindElement)
				bindElement.setAttribute('data-rating', elementRating);

			this.linesVoteHeadSend(elementSessionId, elementRating);

			if (this.BXIM.messenger.popupTooltip)
				this.BXIM.messenger.popupTooltip.close();
		},this);

		return BX.create('div', {props: {className: 'bx-lines-rating-box'}, children: [
			BX.create('div', {props: {className: 'bx-lines-rating-box-current'}, attrs: { style: 'width:'+(rating*20)+'%' }}),
			canVoteHead? BX.create('div', {props: {className: 'bx-lines-rating-box-live'}, children: [
				BX.create('span', {attrs: {'data-rating': 1, 'data-sessionId': sessionId}, props: {className: 'bx-lines-rating-box-item'}, events: {click: ratingSelect}}),
				BX.create('span', {attrs: {'data-rating': 2, 'data-sessionId': sessionId}, props: {className: 'bx-lines-rating-box-item'}, events: {click: ratingSelect}}),
				BX.create('span', {attrs: {'data-rating': 3, 'data-sessionId': sessionId}, props: {className: 'bx-lines-rating-box-item'}, events: {click: ratingSelect}}),
				BX.create('span', {attrs: {'data-rating': 4, 'data-sessionId': sessionId}, props: {className: 'bx-lines-rating-box-item'}, events: {click: ratingSelect}}),
				BX.create('span', {attrs: {'data-rating': 5, 'data-sessionId': sessionId}, props: {className: 'bx-lines-rating-box-item'}, events: {click: ratingSelect}})
			]}): null
		]});
	}

	MessengerCommon.prototype.linesCommentHeadNodes = function(sessionId, comment, canVoteHead, context)
	{
		var result = null;

		if(!context)
		{
			context = 'im';
		}

		if(typeof comment === 'undefined' || comment === null || comment ===  '')
			comment = '';

		canVoteHead = canVoteHead || false;

		var addComment = BX.delegate(function() {
			if (!this.BXIM.messenger.openlines.canUseVoteHead)
			{
				BX.UI.InfoHelper.show('limit_contact_center_ol_boss_rate');
				return false;
			}

			if (this.BXIM.messenger.linesCommentHeadAdd)
			{
				this.BXIM.messenger.linesCommentHeadAdd(null, comment);
			}

			if (this.BXIM.messenger.popupTooltip)
				this.BXIM.messenger.popupTooltip.close();
		},this);

		if(comment === '')
		{
			if(canVoteHead && this.BXIM.messenger.linesCommentHeadAdd)
			{
				result = BX.create('span', {attrs: {'data-sessionId': sessionId, 'data-context': context}, props: {className: 'bx-messenger-content-item-vote-comment-add bx-messenger-ajax'}, html: BX.message('IM_OL_COMMENT_HEAD_ADD'), events: {click: addComment}});
			}
		}
		else
		{
			var commentTitle = comment.replace(/\n/gi, '<br />');

			if(canVoteHead && this.BXIM.messenger.linesCommentHeadAdd)
			{
				result = BX.create('span', {attrs: {'data-sessionId': sessionId, 'data-context': context}, props: {className: 'bx-messenger-content-item-vote-comment-edit bx-messenger-ajax'}, html: commentTitle, events: {click: addComment}});
			}
			else
			{
				result = BX.create('span', {attrs: {'data-sessionId': sessionId, 'data-context': context}, props: {className: 'bx-messenger-content-item-vote-comment-not-edit bx-messenger-ajax'}, html: commentTitle});
			}
		}

		return result;
	}

	MessengerCommon.prototype.linesVoteHeadSend = function(sessionId, rating, comment)
	{
		var result = false;

		if(!rating)
		{
			rating = null;
		}
		if(typeof comment === 'undefined')
		{
			comment = null;
		}

		sessionId = parseInt(sessionId);
		rating = parseInt(rating);

		if(rating <= 0 || rating > 5 || isNaN(rating))
		{
			rating = null;
		}

		if (sessionId > 0 && (rating != null || comment != null))
		{
			if(rating != null)
			{
				if(!this.BXIM.messenger.openlines["voteRatingHead"])
				{
					this.BXIM.messenger.openlines["voteRatingHead"] = {};
				}

				this.BXIM.messenger.openlines["voteRatingHead"][sessionId] = rating;
			}

			if(comment != null)
			{
				if(!this.BXIM.messenger.openlines["voteCommentHead"])
				{
					this.BXIM.messenger.openlines["voteCommentHead"] = {};
				}

				this.BXIM.messenger.openlines["voteCommentHead"][sessionId] = comment;
			}

			BX.ajax({
				url: this.BXIM.pathToAjax+'?LINES_VOTE_SEND&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'COMMAND': 'voteHead', 'SESSION_ID' : sessionId, 'RATING' : rating, 'COMMENT' : comment, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			});

			result = true;
		}

		return result;
	}

	MessengerCommon.prototype.linesCanVoteAsHead = function(lineId)
	{
		if (
			!this.BXIM.messenger.openlines
			|| !this.BXIM.messenger.openlines.canVoteAsHead
			|| !this.BXIM.messenger.openlines.canVoteAsHead[lineId]
		)
		{
			return false;
		}

		return true;
	}

	MessengerCommon.prototype.linesGetCrmPath = function(entityType, entityId)
	{
		if (!this.BXIM.path.crm[entityType] || !this.BXIM.bitrixCrm)
		{
			return '';
		}

		return this.BXIM.path.crm[entityType].replace("#ID#", entityId);
	}

	MessengerCommon.prototype.linesGetSession = function(chatData) // after change this code, sync with IM and MOBILE
	{
		var session = null;
		if (!chatData || chatData.type != "lines")
			return session;

		session = {};
		session.source = this.linesGetSource(chatData);

		var source = chatData.entity_id.toString().split('|');

		session.connector = source[0];
		session.canVoteHead = this.linesCanVoteAsHead(source[1]);

		var sessionData = chatData.entity_data_1.toString().split('|');
		var crmData = chatData.entity_data_2.toString().split('|');

		session.crm = this.BXIM.bitrixCrm && typeof(sessionData[0]) != 'undefined' && sessionData[0] == 'Y'? 'Y': 'N';
		session.crmEntityType = this.BXIM.bitrixCrm && typeof(sessionData[1]) != 'undefined'? sessionData[1]: 'NONE';
		session.crmEntityId = this.BXIM.bitrixCrm && typeof(sessionData[2]) != 'undefined'? sessionData[2]: 0;
		session.crmLink = '';
		session.pin = typeof(sessionData[3]) != 'undefined' && sessionData[3] == 'Y'? 'Y': 'N';
		session.wait = typeof(sessionData[4]) != 'undefined' && sessionData[4] == 'Y'? 'Y': 'N';
		session.id = typeof(sessionData[5]) != 'undefined'? parseInt(sessionData[5]): Math.round(new Date()/1000)+chatData.id;
		session.dateCreate = typeof(sessionData[6]) != 'undefined' || sessionData[6] > 0? parseInt(sessionData[6]): session.id;
		session.lineId = typeof(sessionData[7]) != 'undefined' && sessionData[7] > 0? parseInt(sessionData[7]) : source[1];
		session.blockDate = typeof(sessionData[8]) != 'undefined' || sessionData[8] > 0? parseInt(sessionData[8]) : 0;
		session.blockReason = typeof(sessionData[9]) != 'undefined'? sessionData[9].toUpperCase(): 'NONE';

		session.crmLinkLead = '';
		session.crmLead = 0;
		session.crmLinkCompany = '';
		session.crmCompany = 0;
		session.crmLinkContact = '';
		session.crmContact = 0;
		session.crmLinkDeal = '';
		session.crmDeal = 0;

		if(this.BXIM.bitrixCrm && crmData)
		{
			var index;

			for (index = 0; index < crmData.length; index = index+2)
			{
				if(crmData[index] == 'LEAD' && crmData[index+1] != 0 && crmData[index+1] != 'undefined')
				{
					session.crmLinkLead = this.linesGetCrmPath('LEAD', crmData[index+1]);
					session.crmLead = crmData[index+1];
				}
				if(crmData[index] == 'COMPANY' && crmData[index+1] != 0 && crmData[index+1] != 'undefined')
				{
					session.crmLinkCompany = this.linesGetCrmPath('COMPANY', crmData[index+1]);
					session.crmCompany = crmData[index+1];
				}
				if(crmData[index] == 'CONTACT' && crmData[index+1] != 0 && crmData[index+1] != 'undefined')
				{
					session.crmLinkContact = this.linesGetCrmPath('CONTACT', crmData[index+1]);
					session.crmContact = crmData[index+1];
				}
				if(crmData[index] == 'DEAL' && crmData[index+1] != 0 && crmData[index+1] != 'undefined')
				{
					session.crmLinkDeal = this.linesGetCrmPath('DEAL', crmData[index+1]);
					session.crmDeal = crmData[index+1];
				}
				else
				{
					session.crmDeal = 0;
				}
			}
		}

		if (session.crmEntityType != 'NONE')
		{
			session.crmLink = this.linesGetCrmPath(session.crmEntityType, session.crmEntityId);
		}

		return session;
	};

	MessengerCommon.prototype.linesSetSession = function(chatId, params)
	{
		var session = null;
		if (!this.BXIM.messenger.chat[chatId] || this.BXIM.messenger.chat[chatId].type != "lines")
			return session;

		session = this.linesGetSession(this.BXIM.messenger.chat[chatId]);
		if (typeof(params.crm) != "undefined")
		{
			session.crm = params.crm;
		}
		if (typeof(params.crmEntityType) != "undefined")
		{
			session.crmEntityType = params.crmEntityType;
		}
		if (typeof(params.crmEntityId) != "undefined")
		{
			session.crmEntityId = params.crmEntityId;
		}
		if (typeof(params.pin) != "undefined")
		{
			session.pin = params.pin;
		}
		if (typeof(params.wait) != "undefined")
		{
			session.wait = params.wait;
		}
		if (typeof(params.id) != "undefined")
		{
			session.id = params.id;
		}
		if (typeof(params.dateCreate) != "undefined")
		{
			session.dateCreate = params.dateCreate;
		}
		if (typeof(params.crmLead) != "undefined")
		{
			session.crmLead = params.crmLead;
		}
		if (typeof(params.crmCompany) != "undefined")
		{
			session.crmCompany = params.crmCompany;
		}
		if (typeof(params.crmContact) != "undefined")
		{
			session.crmContact = params.crmContact;
		}
		if (typeof(params.crmDeal) != "undefined")
		{
			session.crmDeal = params.crmDeal;
		}

		this.BXIM.messenger.chat[chatId].entity_data_1 = [session.crm, session.crmEntityType, session.crmEntityId, session.pin, session.wait, session.id, session.dateCreate].join('|');
		this.BXIM.messenger.chat[chatId].entity_data_2 = 'LEAD|' + session.crmLead + '|COMPANY|' + session.crmCompany + '|CONTACT|' + session.crmContact + '|DEAL|' + session.crmDeal;

		return session;
	}

	MessengerCommon.prototype.livechatGetSession = function(chatId)
	{
		var session = null;
		if (!this.BXIM.messenger.chat[chatId] || this.BXIM.messenger.chat[chatId].type != "livechat")
			return session;

		session = {};
		var sessionData = this.BXIM.messenger.chat[chatId].entity_data_1.toString().split('|');

		session.readed = typeof(sessionData[0]) != 'undefined' && sessionData[0] == 'Y'? 'Y': 'N';
		session.readedId = typeof(sessionData[1]) != 'undefined'? sessionData[1]: 0;
		session.readedTime = typeof(sessionData[2]) != 'undefined'? sessionData[2]: false;
		session.sessionId = typeof(sessionData[3]) != 'undefined'? sessionData[3]: 0;
		session.showForm = typeof(sessionData[4]) != 'undefined'? sessionData[4]: 'Y';

		return session;
	}

	MessengerCommon.prototype.linesGetSource = function(chatData) // after change this code, sync with IM and MOBILE
	{
		var sourceId = '';
		if (!chatData || !(chatData.type == 'livechat' || chatData.type == 'lines' || chatData.type == 'support24Question' || chatData.type == 'networkDialog'))
		{
			return sourceId;
		}

		if (chatData.type == 'livechat')
		{
			sourceId = 'livechat';
		}
		else if (chatData.type == 'support24Question')
		{
			sourceId = 'support24Question';
		}
		else
		{
			sourceId = (chatData.entity_id.toString().split('|'))[0];
		}

		if (sourceId == 'skypebot')
		{
			sourceId = 'skype';
		}
		else
		{
			sourceId = sourceId.replace('.', '_');
		}

		return sourceId;
	};

	MessengerCommon.prototype.linesAnswer = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		//if (!BX.MessengerCommon.userInChat(chatId))
		//	return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_ANSWER&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.answer',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'answer', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				//this.BXIM.messenger.chat[chatId].owner = this.BXIM.userId;
				//this.BXIM.messenger.redrawChatHeader();
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesSkip = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_SKIP&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.skip',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'skip', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				if (this.closeSlider())
				{
					return true;
				}
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});

		delete this.BXIM.messenger.chat[chatId];
		delete this.BXIM.messenger.showMessage['chat'+chatId];
	};

	MessengerCommon.prototype.linesActivateSilentMode = function(chatId, flag, force)
	{
		if (!force)
			return false;

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		flag = flag == 'Y'? 'Y': '';
		if (this.BXIM.messenger.chat[chatId].entity_data_3 == flag)
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_ACTIVATE_SILENT_MODE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'silentMode', 'ACTIVATE': flag? 'Y': 'N', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				this.BXIM.messenger.chat[chatId].entity_data_3 = flag;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesActivatePinMode = function(chatId, flag)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		flag = flag == 'Y'? 'Y': 'N';

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_ACTIVATE_PIN_MODE&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.pin',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
				data: {
					timLinesPinAction: flag
				}
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'pinMode', 'ACTIVATE': flag, 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				if(typeof(data.CODE) != "undefined")
				{
					if(data.CODE === 'ERROR_USER_NOT_OPERATOR')
					{
						BX.MessengerCommon.reloadDialogOL();
						BXIM.openMessenger('chat'+chatId);
					}
				}
				else
				{
					BX.MessengerCommon.linesSetSession(chatId, {'pin': flag});
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesCloseDialog = function(chatId, permissionOtherClose)
	{
		if(permissionOtherClose === undefined)
		{
			permissionOtherClose = false;
		}

		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.MessengerCommon.dialogCloseCurrent();

		var command = 'closeDialog';
		if(permissionOtherClose !== false)
		{
			command = 'closeDialogOtherOperator';
		}

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CLOSE_DIALOG&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.finish',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': command, 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				BX.MessengerCommon.linesSetSession(chatId, {'wait': 'Y'});
				this.BXIM.messenger.redrawChatHeader({userRedraw: false});

				if(typeof(data.CODE) != "undefined" && data.CODE === 'ERROR_USER_NOT_OPERATOR')
				{
					BX.MessengerCommon.reloadDialogOL();
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});

		delete this.BXIM.messenger.chat[chatId];
		delete this.BXIM.messenger.showMessage['chat'+chatId];
	};

	MessengerCommon.prototype.linesMarkAsSpam = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_MARK_SPAM&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.spam',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'markSpam', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				this.linesSetSession(chatId, {'id': 0, 'wait': 'Y'});
				this.dialogCloseCurrent();
				this.BXIM.messenger.redrawChatHeader({userRedraw: false});
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});

		delete this.BXIM.messenger.chat[chatId];
		delete this.BXIM.messenger.showMessage['chat'+chatId];
	};

	MessengerCommon.prototype.linesInterceptSession = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_INTERCEPT_SESSION&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.session.intercept',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'interceptSession', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesCreateLead = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		var session = this.linesGetSession(this.BXIM.messenger.chat[chatId]);
		if (!this.BXIM.bitrixCrm || session.crm == 'Y')
		{
			return false;
		}

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CREATE_LEAD&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.crm.create',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'createLead', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				this.BXIM.messenger.blockJoinChat[chatId] = false;

				if(typeof(data.CODE) != "undefined" && data.CODE === 'ERROR_USER_NOT_OPERATOR')
				{
					BX.MessengerCommon.reloadDialogOL();
					BXIM.openMessenger('chat'+chatId);
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesChangeCrmEntity = function(messageId)
	{
		if (!this.BXIM.messenger.message[messageId])
			return false;

		var chatId = this.BXIM.messenger.message[messageId].chatId;
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		var session = this.linesGetSession(this.BXIM.messenger.chat[chatId]);
		if (!this.BXIM.bitrixCrm || session.crm == 'N')
			return false;

		this.linesChangeCrmEntityMessageId = messageId;

		if (window.obCrm && window.obCrm.olCrmSelector)
		{
			window.obCrm.olCrmSelector.Open();
		}
		else
		{
			BX.ajax({
				url: BXIM.pathToAjax+'?CRM_SELECTOR&V='+BXIM.revision,
				method: 'POST',
				timeout: 30,
				data: {'IM_CRM_SELECTOR' : 'Y', 'sessid': BX.bitrix_sessid()}
			});

			BX.addCustomEvent('onCrmSelectorInit', function(id, name, object){
				if (name != 'olCrmSelector')
					return true;

				setTimeout(function(){
					window.obCrm[name].Open();
					window.obCrm[name].AddOnSaveListener(function(result)
					{
						BX.MessengerCommon.linesChangeCrmEntityAjax(result);
					});
				}, 200);
			});
		}
	}

	MessengerCommon.prototype.linesChangeCrmEntityAjax = function(result)
	{
		if (!this.BXIM.bitrixCrm)
		{
			return false;
		}

		var found = false;
		for(var i in result['company'])
		{
			found = result['company'][i];
		}
		if (!found)
		{
			for(var i in result['contact'])
			{
				found = result['contact'][i];
			}
		}
		if (!found)
		{
			for(var i in result['lead'])
			{
				found = result['lead'][i];
			}
		}
		if (!found)
		{
			return false;
		}

		var messageId = this.linesChangeCrmEntityMessageId;
		if (!this.BXIM.messenger.message[messageId])
			return false;

		var chatId = this.BXIM.messenger.message[messageId].chatId;
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		var entityId = found.id.split('_')[1];
		var entityType = found.type;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CHANGE_CRM_ENTITY&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.crm.change',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'changeCrmEntity', 'CHAT_ID' : chatId, 'MESSAGE_ID': messageId, 'ENTITY_TYPE' : entityType, 'ENTITY_ID': entityId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	}

	MessengerCommon.prototype.linesCancelCrmExtend = function(messageId)
	{
		if (!this.BXIM.bitrixCrm)
		{
			return false;
		}

		if (!this.BXIM.messenger.message[messageId])
			return false;

		var chatId = this.BXIM.messenger.message[messageId].chatId;
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CANCEL_CRM_EXTEND&V='+this.BXIM.revision+'&logTag='+BX.MessengerCommon.getLogTrackingParams({
				name: 'imopenlines.operator.crm.cancel',
				dialog: BX.MessengerCommon.getDialogDataForTracking('chat'+chatId),
			}),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'cancelCrmExtend', 'CHAT_ID' : chatId, 'MESSAGE_ID': messageId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});

		var keyboard = BX('im-message-keyboard-'+messageId);
		if (keyboard)
		{
			keyboard.innerHTML = '';
			keyboard.id = 'im-message-keyboard-empty-'+messageId;
			keyboard.className = '';
		}
	}

	MessengerCommon.prototype.getMessageParam = function(messageId, name, defaultValue)
	{
		var params = this.getMessageParams(messageId);
		if (!params)
		{
			return defaultValue;
		}

		if (typeof (params[name]) === 'undefined')
		{
			return defaultValue;
		}

		return params[name];
	}

	MessengerCommon.prototype.getMessageParams = function(messageId)
	{
		if (typeof(this.BXIM.messenger.message[messageId]) === 'undefined')
		{
			return null;
		}

		var message = this.BXIM.messenger.message[messageId];

		if (typeof(message.params) === 'undefined')
		{
			return {};
		}

		return message.params;
	}

	/**
	 * @deprecated
	 */
	MessengerCommon.prototype.getMessagePlural = function(messageId, number)
	{
		return BX.Loc.getMessagePlural(messageId, parseInt(number));
	}

	MessengerCommon.prototype.openStore = function(additionalParams)
	{
		if (!BX.MessengerCommon.isSliderSupport())
		{
			if (this.isDesktop())
			{
				BX.desktop.browse('/online/?IM_DIALOG=' + this.BXIM.messenger.currentTab);
			}
			else
			{
				this.BXIM.openConfirm(BX.message('IM_FUNCTION_FOR_BROWSER'));
			}
			return false;
		}
		else
		{
			var dialogId = this.getDialogId();
			var session = this.linesGetSession(this.BXIM.messenger.chat[dialogId.substr(4)]);
			var params = {
				dialogId: dialogId,
				sessionId: session.id,
				ownerId: session.crmDeal,
				context: 'chat',
				st: {
					tool: 'crm',
					category: 'payments',
					event: 'payment_create_click',
					c_section: 'chats',
					c_sub_section: 'web',
					type: 'delivery_payment',
				}
			};
			Object.assign(params, additionalParams);
			var salescenterUrl = BX.util.add_url_param('/saleshub/app/', params);
			if (params['compilationId'])
			{
				BX.SidePanel.Instance.destroy(salescenterUrl);
			}
			BX.SidePanel.Instance.open(salescenterUrl, {allowChangeHistory: false, width: 1140});
		}
	}

	MessengerCommon.prototype.sendCompilationByChat = function(compilationId)
	{
		BX.ajax.runAction('salescenter.compilation.sendCompilationByChat', {
			data: {
				compilationId
			},
		})
	}

	MessengerCommon.prototype.openRenamePortal = function(button)
	{
		if (button && BX.hasClass(button, 'bx-messenger-keyboard-button-block'))
		{
			return false;
		}

		if (this.isMobile())
		{
			app.alert({'text': BX.message('IM_FUNCTION_FOR_BROWSER')});
		}
		if (this.isDesktop())
		{
			BX.desktop.browse(this.BXIM.path.profile+'?b24renameform=1', "desktopApp");
		}
		else if (typeof(BX.Bitrix24) != 'undefined')
		{
			BX.Bitrix24.renamePortal()
		}
		else
		{
			this.BXIM.openConfirm(BX.message('IM_UNKNOWN_ERROR'));
		}
		return true;
	}

	MessengerCommon.prototype.updateUserData = function(params)
	{
		var i;
		if(BX.type.isPlainObject(params.users))
		{
			for (i in params.users)
			{
				params.users[i].last_activity_date = params.users[i].last_activity_date? new Date(params.users[i].last_activity_date): false;
				params.users[i].mobile_last_date = params.users[i].mobile_last_date? new Date(params.users[i].mobile_last_date): false;
				params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
				params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

				this.BXIM.messenger.users[i] = params.users[i];
			}
		}

		if(BX.type.isPlainObject(params.hrphoto))
		{
			for (i in params.hrphoto)
			{
				this.BXIM.messenger.hrphoto[i] = params.hrphoto[i];
			}
		}

		if(BX.type.isPlainObject(params.chat))
		{
			for (i in params.chat)
			{
				params.chat[i].date_create = new Date(params.chat[i].date_create);
				this.BXIM.messenger.chat[i] = params.chat[i];
			}
		}

		if(BX.type.isPlainObject(params.userInChat))
		{
			for (i in params.userInChat)
			{
				this.BXIM.messenger.userInChat[i] = params.userInChat[i];
			}
		}
	}

	BX.MessengerCommon = new MessengerCommon();


	/* Time queue API */
	var MessengerTimer = function()
	{
		this.list = {};

		this.updateInterval = 1000;

		clearInterval(this.updateIntervalId);
		this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval)
	};

	MessengerTimer.prototype.start = function(type, id, time, callback, callbackParams)
	{
		id = id === null? 'default': id;

		time = parseInt(time);
		if (time <= 0 || id.toString().length <= 0)
		{
			return false;
		}

		if (typeof this.list[type] == 'undefined')
		{
			this.list[type] = {};
		}

		this.list[type][id] = {
			'dateStop': new Date().getTime()+time,
			'callback': typeof callback == 'function'? callback: function() {},
			'callbackParams': typeof callbackParams == 'undefined'? {}: callbackParams
		};

		return true;
	};

	MessengerTimer.prototype.stop = function(type, id, skipCallback)
	{
		id = id === null? 'default': id;

		if (id.toString().length <= 0 || typeof this.list[type] == 'undefined')
		{
			return false;
		}

		if (!this.list[type][id])
		{
			return true;
		}

		if (skipCallback !== true)
		{
			this.list[type][id]['callback'](id, this.list[type][id]['callbackParams']);
		}

		delete this.list[type][id];

		return true;
	};

	MessengerTimer.prototype.stopAll = function(skipCallback)
	{
		for (var type in this.list)
		{
			if (this.list.hasOwnProperty(type))
			{
				for (var id in this.list[type])
				{
					if(this.list[type].hasOwnProperty(id))
					{
						this.stop(type, id, skipCallback);
					}
				}
			}
		}
		return true;
	};

	MessengerTimer.prototype.worker = function()
	{
		for (var type in this.list)
		{
			if (!this.list.hasOwnProperty(type))
			{
				continue;
			}
			for (var id in this.list[type])
			{
				if(!this.list[type].hasOwnProperty(id) || this.list[type][id]['dateStop'] > new Date())
				{
					continue;
				}
				this.stop(type, id);
			}
		}
		return true;
	};

	MessengerTimer.prototype.destroy = function()
	{
		clearInterval(this.updateIntervalId);
		this.stopAll(true);
		return true;
	};

	BX.MessengerTimer = new MessengerTimer();


})(window);