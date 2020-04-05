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
	};

	/* Section: Context */
	MessengerCommon.prototype.setBxIm = function(dom)
	{
		this.BXIM = dom;
	}

	MessengerCommon.prototype.isPage = function()
	{
		return typeof(BX.MessengerWindow) != 'undefined' && !(this.BXIM.context == 'POPUP-FULLSCREEN' && BX.browser.IsMobile());
	}

	MessengerCommon.prototype.isDesktop = function()
	{
		return typeof(BX.desktop) != 'undefined' && BX.desktop.apiReady;
	}

	MessengerCommon.prototype.isMobile = function()
	{
		return this.BXIM.mobileVersion;
	}

	MessengerCommon.prototype.isMobileNative = function()
	{
		return false;
	}

	MessengerCommon.prototype.isLinesOperator = function()
	{
		return this.BXIM.messenger.openlines && this.BXIM.messenger.openlines.queue && this.BXIM.messenger.openlines.queue.length > 0;
	}

	MessengerCommon.prototype.isBot = function(botId)
	{
		return typeof(this.BXIM.messenger.bot[botId]) != 'undefined';
	}

	MessengerCommon.prototype.isBirthday = function(birthday) // after change this code, sync with IM and MOBILE
	{
		var date = new Date();
		var currentDate = ("0" + date.getDate().toString()).substr(-2)+'-'+("0" + (date.getMonth() + 1).toString()).substr(-2);
		return birthday == currentDate;
	};

	MessengerCommon.prototype.getDebugInfo = function()
	{
		return {
			context: this.BXIM.context,
			design: this.BXIM.design,
			isDesktop: this.isDesktop() ? 'Y' : 'N',
			isPage: this.isPage() ? 'Y' : 'N',
			isMobile: this.isMobile() ? 'Y' : 'N',
			vInitedCall: BX.localStorage.get('vInitedCall') ? 'Y' : 'N',
			desktopStatus: this.BXIM.desktopStatus ? 'Y' : 'N',
			callInit: this.BXIM.webrtc.callInit ? 'Y' : 'N',
			callActive: this.BXIM.webrtc.callActive ? 'Y' : 'N',
			appVersion: navigator.appVersion
		}
	}

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
		console.log(mute? 'Y': 'N');

		this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] = mute;
		this.BXIM.messenger.chat[chatId].mute_list[this.BXIM.userId] = mute;

		this.BXIM.messenger.dialogStatusRedraw();
		this.BXIM.messenger.updateMessageCount();

		if (sendAjax)
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?CHAT_MUTE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_CHAT_MUTE' : 'Y', 'CHAT_ID': chatId, 'MUTE': this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]? 'Y':'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
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

		scroll = scroll !== false;
		max = 400;//parseInt(max);

		var lastUnreadMessage = this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab] && this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab][0]? BX('im-message-'+this.BXIM.messenger.unreadMessage[this.BXIM.messenger.currentTab][0]): null;
		if (lastUnreadMessage)
		{
			var visibleNode = lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode.previousElementSibling? lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode.previousElementSibling: lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode;
			var scrollResult = this.isElementVisibleOnScreen(visibleNode, element, true);
			if (!scrollResult.top)
			{
				BX.scrollToNode(lastUnreadMessage.parentNode.parentNode.parentNode.parentNode.parentNode);
				return false;
			}
		}

		return (scroll && this.isScrollMax(element, max));
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
				["", BX.date.convertBitrixFormat(BX.message("IM_M_MESSAGE_TITLE_FORMAT_DATE"))]
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
				["", BX.date.convertBitrixFormat(BX.message("IM_CL_RESENT_FORMAT_DATE"))]
			]
		}
		else if (type == 'RECENT_OL_TITLE')
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.date.convertBitrixFormat(BX.message("IM_CL_RESENT_FORMAT_DATE"))]
			]
		}
		else
		{
			format = [
				["tommorow", "tommorow, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["today", "today, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["yesterday", "yesterday, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))]
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
		}
		return BX.date.format(format, Math.round(date.getTime()/1000)+parseInt(BX.message("SERVER_TZ_OFFSET"))+parseInt(BX.message("USER_TZ_OFFSET")), Math.round((new Date).getTime()/1000)+parseInt(BX.message("SERVER_TZ_OFFSET"))+parseInt(BX.message("USER_TZ_OFFSET")), true);
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

	/* Section: Images */
	MessengerCommon.prototype.isBlankAvatar = function(url)
	{
		return url == '' || url.indexOf(this.BXIM.pathToBlankImage) >= 0;
	};

	MessengerCommon.prototype.getDefaultAvatar = function(type)
	{
		return "/bitrix/js/im/images/default-avatar-"+type+".png";
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
			element.parentNode.parentNode.className = ''
			element.parentNode.parentNode.innerHTML = '<a href="'+link+'" target="_blank">'+link+'</a>';
		}

		return true;
	};



	/* Section: Text */
	MessengerCommon.prototype.prepareText = function(text, prepare, quote, image, highlightText)
	{
		var textElement = text;
		prepare = prepare == true;
		quote = quote == true;
		image = image == true;
		highlightText = highlightText? highlightText: false;

		textElement = BX.util.trim(textElement);

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
		if (prepare)
		{
			textElement = BX.util.htmlspecialchars(textElement);
		}

		textElement = this.decodeBbCode(textElement, quote);

		if (quote)
		{
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, p4, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\"><div class=\"bx-messenger-content-quote-name\">"+p1+" <span class=\"bx-messenger-content-quote-time\">"+p2+"</span></div>"+p3+"</div></div><br />";
			});
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">"+p1+"</div></div><br />";
			});
		}
		if (prepare)
		{
			textElement = textElement.replace(/\n/gi, '<br />');
		}
		textElement = textElement.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		if (image)
		{
			var changed = false;
			textElement = textElement.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/ig, function(whole, aInner, text, offset)
			{
				if(!text.match(/(\.(jpg|jpeg|png|gif)\?|\.(jpg|jpeg|png|gif)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0)
				{
					return whole;
				}
				else if (BX.MessengerCommon.isMobile())
				{
					changed = true;
					return (offset > 0? '<br />':'')+'<span class="bx-messenger-file-image"><span class="bx-messenger-file-image-src"><img src="'+text+'" class="bx-messenger-file-image-text" onclick="BXIM.messenger.openPhotoGallery(this.src);" onerror="BX.MessengerCommon.hideErrorImage(this)"></span></span>';
				}
				else
				{
					changed = true;
					return (offset > 0? '<br />':'')+'<span class="bx-messenger-file-image"><a' +aInner+ ' target="_blank" class="bx-messenger-file-image-src"><img src="'+text+'" class="bx-messenger-file-image-text" onerror="BX.MessengerCommon.hideErrorImage(this)"></a></span>';
				}
			});
			if (changed)
			{
				textElement = textElement.replace(/<\/span>(\n?)<br(\s\/?)>/ig, '</span>').replace(/<br(\s\/?)>(\n?)<br(\s\/?)>(\n?)<span/ig, '<br /><span');
			}
		}
		if (highlightText)
		{
			textElement = textElement.replace(new RegExp("("+highlightText.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'), '<span class="bx-messenger-highlight">$1</span>');
		}

		if (this.BXIM.settings.enableBigSmile)
		{
			textElement = textElement.replace(
				/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/,
				function doubleSmileSize(match, start, width, middle, height, end) {
					return start + (parseInt(width, 10) * 2) + middle + (parseInt(height, 10) * 2) + end;
				}
			);
		}

		if (textElement.substr(-6) == '<br />')
		{
			textElement = textElement.substr(0, textElement.length-6);
		}
		textElement = textElement.replace(/<br><br \/>/ig, '<br />');
		textElement = textElement.replace(/<br \/><br>/ig, '<br />');

		return textElement;
	};

	MessengerCommon.prototype.trimText = function(text)
	{
		return BX.util.trim(text);
	};

	MessengerCommon.prototype.purifyText = function(text, params) // after change this code, sync with IM and MOBILE
	{
		if (!text)
		{
			return '';
		}

		text = text.toString();
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
		text = text.replace(/<br><br \/>/ig, '<br />');
		text = text.replace(/<br \/><br>/ig, '<br />');
		text = text.replace(/\[[buis]\](.*?)\[\/[buis]\]/ig, '$1');
		text = text.replace(/\[url\](.*?)\[\/url\]/ig, '$1');
		text = text.replace(/\[RATING=([1-5]{1})\]/ig, function(whole, rating) {return '['+BX.message('IM_F_RATING')+'] ';});
		text = text.replace(/\[ATTACH=([0-9]{1,})\]/ig, function(whole, rating) {return '['+BX.message('IM_F_ATTACH')+'] ';});
		text = text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, '$2');
		text = text.replace(/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/ig, '$2');
		text = text.replace(/\[SEND=([0-9]{1,})\](.*?)\[\/SEND\]/ig, '$2');
		text = text.replace(/\[PUT=([0-9]{1,})\](.*?)\[\/PUT\]/ig, '$2');
		text = text.replace(/\[CALL=([0-9]{1,})\](.*?)\[\/CALL\]/ig, '$2');
		text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, '$2');
		text = text.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
		text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/ig, '($1)');
		text = text.replace(/<img.*?title="([^"]*)".*?>/ig, '($1)');
		text = text.replace(/\[ATTACH=([0-9]{1,})\]/ig, function(whole, command, text) {return command == 10000? '': '['+BX.message('IM_F_ATTACH')+'] ';});
		text = text.replace(/<s>([^"]*)<\/s>/ig, ' ');
		text = text.replace(/\[s\]([^"]*)\[\/s\]/ig, ' ');
		text = text.replace(/\[icon\=([^\]]*)\]/ig, function(whole)
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
		text = text.replace('<br />', ' ').replace(/<\/?[^>]+>/gi, '').replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, " ["+BX.message("IM_M_QUOTE_BLOCK")+"] ");

		text = this.trimText(text);

		if (text.length <= 0)
		{
			if (params && params.FILE_ID && params.FILE_ID.length > 0)
			{
				text = '['+BX.message('IM_F_FILE')+']';
			}
			else if (params && params.ATTACH && params.ATTACH.length > 0)
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

	MessengerCommon.prototype.decodeBbCode = function(textElement, textOnly)
	{
		textOnly = typeof(textOnly)? false: textOnly;

		textElement = textElement.replace(/\[LIKE\]/ig, '<span class="bx-smile bx-im-smile-like" title="'+BX.message('IM_MESSAGE_LIKE')+'"></span>');
		textElement = textElement.replace(/\[DISLIKE\]/ig, '<span class="bx-smile bx-im-smile-dislike" title="'+BX.message('IM_MESSAGE_DISLIKE')+'"></span>');

		textElement = textElement.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, BX.delegate(function(whole, userId, text)
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

		textElement = textElement.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function(whole, openlines, chatId, text)
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

		textElement = textElement.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, function(whole, historyId, text)
		{
			var html = '';

			historyId = parseInt(historyId);
			if (!textOnly && text && historyId > 0)
				html = '<span class="bx-messenger-ajax" data-entity="phoneCallHistory" data-historyId="'+historyId+'">'+text+'</span>';
			else
				html = text;

			return html;
		});

		textElement = textElement.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, function(whole, command, text)
		{
			var html = '';

			text = text? text: command;
			command = command? command: text;

			if (!textOnly && text)
			{
				text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
				text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

				html = '<span class="bx-messenger-command" data-entity="send" title="'+BX.message('IM_BB_SEND')+'">'+text+'</span>';
				html += '<span class="bx-messenger-command-data">'+command+'</span>';
			}
			else
			{
				html = text;
			}

			return html;
		});
		textElement = textElement.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, function(whole, command, text)
		{
			var html = '';

			text = text? text: command;
			command = command? command: text;

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
		textElement = textElement.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, function(whole, command, text)
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
			var textElementSize = BX.util.trim(textElement.replace(/\[icon\=([^\]]*)\]/ig, '')).length;
		}
		textElement = textElement.replace(/\[icon\=([^\]]*)\]/ig, BX.delegate(function(whole)
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

		textElement = textElement.replace(/\[RATING\=([1-5]{1})\]/ig, BX.delegate(function(whole, rating)
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

		return textElement;
	}

	MessengerCommon.prototype.prepareTextBack = function(text, trueQuote)
	{
		var textElement = text;

		trueQuote = trueQuote === true;

		textElement = BX.util.htmlspecialcharsback(textElement);
		textElement = textElement.replace(/<(\/*)([buis]+)>/ig, '[$1$2]');
		textElement = textElement.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
		textElement = textElement.replace(/<a.*?href="([^"]*)".*?>.*?<\/a>/ig, '$1');
		if (!trueQuote)
		{
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

		if (userId.toString().substr(0, 4) == 'chat' || userId.toString().substr(0, 2) == 'sg')
		{
			var chatId = userId.toString().substr(0, 4) == 'chat'? userId.toString().substr(4): userId;
			if (reset || !(this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].id))
			{
				this.BXIM.messenger.chat[chatId] = {'id': chatId, 'name': BX.message('IM_M_LOAD_USER'), 'owner': 0, work_position: '', 'avatar': this.BXIM.pathToBlankImage, 'type': 'chat', color: '#556574', 'fake': true, date_create: false};
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
				this.BXIM.messenger.users[userId] = {'id': userId, 'avatar': this.BXIM.pathToBlankImage, 'name': BX.message('IM_M_LOAD_USER'), 'profile': profilePath, 'status': 'guest', work_position: '', 'extranet': false, 'network': false, color: '#556574', 'fake': true, last_activity_date: new Date(0), mobile_last_date: new Date(0), absent: false, idle: false};
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
		if (!this.BXIM.messenger.userInChat[chatId])
			return false;

		if (typeof(userId) == 'undefined')
		{
			userId = this.BXIM.userId;
		}
		else
		{
			userId = parseInt(userId);
		}

		var userFound = false;
		if (typeof(this.BXIM.messenger.userInChat[chatId].indexOf) != 'undefined')
		{
			if (
				this.BXIM.messenger.userInChat[chatId].indexOf(userId.toString()) > -1
				|| this.BXIM.messenger.userInChat[chatId].indexOf(parseInt(userId)) > -1
			)
			{
				userFound = true;
			}
		}
		else // TODO delete if not support IE 8
		{
			for (var i = 0; i < this.BXIM.messenger.userInChat[chatId].length; i++)
			{
				if (parseInt(this.BXIM.messenger.userInChat[chatId][i]) == parseInt(userId))
				{
					userFound = true;
					break;
				}
			}
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

		var status = '';
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
			statusText = BX.message('IM_STATUS_NETWORK');
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
			status = userData.status? userData.status.toString(): '';
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
			status = 'idle';
			statusText = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userData));
		}
		else
		{
			status = userData.status? userData.status.toString(): '';
			statusText = BX.message('IM_STATUS_'+status.toUpperCase());
		}

		if (userData && this.isBirthday(userData.birthday) && (userData.status == 'online' || !online.isOnline))
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
				userData.mobile_last_date = new Date(0);
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
			message = this.formatDate(userData.idle, format)

			// TODO need new phrases for IDLE for use new method
			//message = BX.date.formatLastActivityDate(this.BXIM.messenger.users[userId].idle);
		}

		return message;
	}

	MessengerCommon.prototype.getUserMobileStatus = function(userData) // after change this code, sync with IM and MOBILE
	{
		if (!userData)
			return false;

		var status = false;
		var mobile_last_date = userData.mobile_last_date;
		var last_activity_date = userData.last_activity_date;
		if (
			(new Date())-mobile_last_date < BX.user.getSecondsForLimitOnline()*1000
			&& last_activity_date-mobile_last_date < 300*1000
		)
		{
			status = true;
		}

		return status;
	};

	MessengerCommon.prototype.getUserIdleStatus = function(userData, online) // after change this code, sync with IM and MOBILE
	{
		if (!userData)
			return '';

		online = online? online: BX.user.getOnlineStatus(userData.last_activity_date);

		return userData.idle && online.isOnline;
	};

	MessengerCommon.prototype.getUserPosition = function(userData, recent) // after change this code, sync with IM and MOBILE
	{
		recent = recent === true;

		if (!userData)
			return '';

		var position = '';
		if (recent && userData.last_activity_date && !(userData.bot || userData.network))
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
		else if (userData.absent)
		{
			online = this.getOnlineData(userData);
			text = BX.message('IM_STATUS_VACATION_TITLE').replace('#DATE#',
				BX.date.format(BX.date.convertBitrixFormat(BX.message("FORMAT_DATE")), userData.absent.getTime()/1000)
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
			if (online.isOnline && userData.idle)
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

		if (typeof(this.BXIM.messenger.userChatOptions[entityType]) == 'undefined')
			return null;

		if (!this.BXIM.messenger.userChatOptions[entityType]['PATH'])
			return null;

		return {'PATH': this.BXIM.messenger.userChatOptions[entityType]['PATH'].replace('#ID#', this.BXIM.messenger.chat[chatId].entity_id), 'TITLE': this.BXIM.messenger.userChatOptions[entityType]['PATH_TITLE']};
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
				if (!this.BXIM.ppServerStatus)
				{
					BX.PULL.updateState(true);
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

		if (this.BXIM.messenger.recentList && this.BXIM.messenger.contactListSearchText != null && this.BXIM.messenger.contactListSearchText.length == 0)
		{
			this.recentListRedraw(params);
		}
		else if (this.BXIM.messenger.chatList)
		{
			this.chatListRedraw(params);
		}
		else
		{
			this.contactListRedraw(params);
			if (this.BXIM.messenger.recentListExternal)
			{
				this.recentListRedraw(params);
			}
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

			if (this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
			{
				this.BXIM.messenger.popupPopupMenu.close();
			}
		}

		if (this.BXIM.messenger.contactListSearchText.length > 0)
		{
			this.contactListPrepareSearch('contactList', this.BXIM.messenger.popupContactListElementsWrap, this.BXIM.messenger.contactListSearchText, params.FORCE? {}: {params: false, timeout: this.isMobile()? 500: 100})
		}
		else
		{
			if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
				clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

			this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
			this.BXIM.messenger.popupContactListElementsWrap.appendChild(this.contactListPrepare());

			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}

		params.SEND = params.SEND == true;
		if (!this.isMobile() && params.SEND)
		{
			BX.localStorage.set('mrd', {viewGroup: this.BXIM.settings.viewGroup, viewOffline: this.BXIM.settings.viewOffline}, 5);
		}
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
			params.viewOnlyBusiness = true;
			params.viewChat = false;
			params.viewOfflineWithPhones = false;
		}

		var searchParams = {
			'listName': name,
			'groupOpen': true,
			'viewSelf': name == 'contactList',
			'viewOffline': true,
			'viewOnlyBusiness': false,
			'viewGroup': true,
			'viewChat': true,
			'viewBot': true,
			'viewTransferViQueue': false,
			'viewTransferOlQueue': false,
			'viewOpenChat': true,
			'viewOfflineWithPhones': false,
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
			this.BXIM.messenger.contactList = false;
			this.BXIM.messenger.contactListShowed = {};
			this.BXIM.messenger.realSearch = false;

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
		return BX.PreventDefault(e);
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
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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
						if (this.BXIM.webrtc.popupTransferDialogContactListElements != null)
						{
							this.contactListPrepareSearch('popupTransferDialogContactListElements', this.BXIM.webrtc.popupTransferDialogContactListElements, this.BXIM.webrtc.popupTransferDialogContactListSearch.value, {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewBot': false, 'viewOnlyIntranet': true, 'viewOfflineWithPhones': true});
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

						data.USERS[i].last_activity_date = new Date(data.USERS[i].mobile_last_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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

		this.BXIM.messenger.realSearch = false;
		this.BXIM.messenger.realSearchFound = true;

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
			if (event.keyCode == 27)
			{
				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}

				if (this.BXIM.messenger.contactListSearchText <= 0 && !this.BXIM.messenger.chatList)
				{
					this.BXIM.messenger.popupContactListSearchInput.value = "";
					if (!this.isMobile() && this.BXIM.messenger.popupMessenger && !this.BXIM.messenger.desktop.ready() && !this.BXIM.messenger.webrtc.callInit)
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
			this.BXIM.messenger.contactList = true;

			if (event.keyCode == 13)
			{
				var clearSearch = true;

				var item = BX.findChildByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-item");
				if (item)
				{
					this.recentListElementToTop(item.getAttribute('data-userId'));
					this.BXIM.messenger.openMessenger(item.getAttribute('data-userid'));
				}
				else
				{
					var item = BX.findChildByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-chatlist-search-button");
					if (item)
					{
						clearSearch = false;
						this.BXIM.messenger.chatListSearchAction(item);
						return true;
					}
				}

				if (clearSearch)
				{
					if (this.BXIM.messenger.realSearch)
					{
						this.BXIM.messenger.realSearchFound = true;
					}
					this.BXIM.messenger.popupContactListSearchInput.value = '';
				}
			}
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
				this.BXIM.messenger.realSearch = false;
			}

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = true;
			this.BXIM.messenger.contactList = false;

			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
			BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
			this.BXIM.messenger.popupContactListActive = false;
			this.BXIM.messenger.popupContactListHovered = false;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
		}
		else
		{
			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active');
			BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-hover bx-messenger-box-contact-normal');
			this.BXIM.messenger.popupContactListActive = true;
			this.BXIM.messenger.popupContactListHovered = true;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

			this.contactListRealSearch(this.BXIM.messenger.contactListSearchText);
		}
		this.userListRedraw();
	};



	/* Section: Recent list */
	MessengerCommon.prototype.recentListRedraw = function(params)
	{
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.MobileActionNotEqual('RECENT'))
			return false;

		if (this.BXIM.messenger.recentList && this.BXIM.messenger.popupMessenger)
		{
			if (!this.isMobile())
			{
				if (this.BXIM.messenger.popupMessenger == null)
					return false;

				this.BXIM.messenger.chatList = false;
				this.BXIM.messenger.recentList = true;
				this.BXIM.messenger.contactList = false;
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

			this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
			if (this.isPage() && BX.MessengerWindow.currentTab == 'im-ol')
			{
				BX.addClass(this.BXIM.messenger.popupContactListElementsWrap, 'bx-messenger-recent-lines-wrap');
				this.BXIM.messenger.popupContactListElementsWrap.appendChild(this.recentLinesListPrepare(params));
			}
			else
			{
				BX.removeClass(this.BXIM.messenger.popupContactListElementsWrap, 'bx-messenger-recent-lines-wrap');
				this.BXIM.messenger.popupContactListElementsWrap.appendChild(this.recentListPrepare(params));
			}

			if (this.BXIM.messenger.recentListExternal)
			{
				this.BXIM.messenger.recentListExternal.innerHTML = this.BXIM.messenger.popupContactListElementsWrap.innerHTML;
			}

			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}
		else if (this.BXIM.messenger.recentListExternal)
		{
			this.BXIM.messenger.recentListExternal.innerHTML = '';
			this.BXIM.messenger.recentListExternal.appendChild(this.recentListPrepare(params));
		}
	};

	MessengerCommon.prototype.recentListPrepare = function(params)
	{
		var recentList = document.createDocumentFragment();

		var groups = {};
		params = typeof(params) == 'object'? params: {};

		var showOnlyChat = params.showOnlyChat;

		if (!this.BXIM.messenger.recentListLoad)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			}));

			this.recentListGetFromServer();
			return recentList;
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		for (var dialogId in this.BXIM.messenger.unreadMessage)
		{
			if (this.inRecentList(dialogId))
				continue;

			if (dialogId.toString().substr(0,4) == 'chat')
			{
				var user = this.BXIM.messenger.chat[dialogId.toString().substr(4)];
				if (user && user.entity_type == 'LINES' && this.BXIM.settings.linesTabEnable)
				{
					continue;
				}
			}
			else
			{
				var user = this.BXIM.messenger.users[dialogId];
			}

			if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined')
			{
				this.readMessage(dialogId, true, true);
				continue;
			}

			var maxElement = Math.max.apply(Math, this.BXIM.messenger.unreadMessage[dialogId]);
			if (this.BXIM.messenger.message[maxElement])
			{
				this.BXIM.messenger.recent.push({
					chatId: this.BXIM.messenger.message[maxElement].chatId,
					date: this.BXIM.messenger.message[maxElement].date,
					id: maxElement,
					params: {},
					recipientId: dialogId.toString().substr(0,4) == 'chat'? dialogId: this.BXIM.userId,
					senderId: this.BXIM.messenger.message[maxElement].senderId,
					text: this.BXIM.messenger.message[maxElement].text,
					userId: dialogId,
					userIsChat: dialogId.toString().substr(0,4) == 'chat',
				});
			}
		}

		this.BXIM.messenger.recent.sort(function(i, ii) {
			var i1 = i.date.getTime();
			var i2 = ii.date.getTime();

			if (i1 > i2) { return -1; }
			else if (i1 < i2) { return 1;}
			else{
				if (i > ii) { return -1; }
				else if (i < ii) { return 1;}
				else{ return 0;}
			}
		});


		this.BXIM.messenger.recentListIndex = [];
		var limit = this.isMobile()? 49: 999999;

		var userInList = {};
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (!this.BXIM.messenger.recent[i].pinned)
			{
				continue;
			}
			if (typeof(this.BXIM.messenger.recent[i].userIsChat) == 'undefined')
			{
				this.BXIM.messenger.recent[i].userIsChat = this.BXIM.messenger.recent[i].recipientId.toString().substr(0,4) == 'chat';
			}

			var item = BX.clone(this.BXIM.messenger.recent[i]);

			if (i > limit)
			{
				if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
				{
					continue;
				}
			}

			var chatStatus = '';
			if (item.userIsChat)
			{
				var user = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined' || this.isPage() &&  user.entity_type == 'LINES' && this.BXIM.settings.linesTabEnable && this.isLinesOperator())
					continue;

				var userId = 'chat'+user.id;
			}
			else if (!showOnlyChat)
			{
				var user = this.BXIM.messenger.users[item.userId];
				if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined')
					continue;

				if (typeof(user.active) != 'undefined' && !user.active && !this.BXIM.messenger.unreadMessage[user.id])
					continue;

				var userId = user.id;
			}
			else
			{
				continue;
			}

			userInList[userId] = true;

			if (!groups['favorites'])
			{
				groups['favorites'] = true;
				recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group bx-messenger-recent-group-pinned"}, children : [
					BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RECENT_PINNED')})
				]}));
			}

			recentList.appendChild(this.drawContactListElement({
				'id': userId,
				'data': user,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params,
				'pinned': item.pinned
			}));
			this.BXIM.messenger.recentListIndex.push(userId);
		}

		var groups = {};
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].pinned)
			{
				continue;
			}
			if (typeof(this.BXIM.messenger.recent[i].userIsChat) == 'undefined')
			{
				this.BXIM.messenger.recent[i].userIsChat = this.BXIM.messenger.recent[i].recipientId.toString().substr(0,4) == 'chat';
			}

			var item = BX.clone(this.BXIM.messenger.recent[i]);

			if (i > limit)
			{
				if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
				{
					continue;
				}
			}

			var chatStatus = '';
			if (item.userIsChat)
			{
				var user = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined' || this.isPage() &&  user.entity_type == 'LINES' && this.BXIM.settings.linesTabEnable && this.isLinesOperator())
					continue;

				var userId = 'chat'+user.id;
			}
			else if (!showOnlyChat)
			{
				var user = this.BXIM.messenger.users[item.userId];
				if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined')
					continue;

				if (typeof(user.active) != 'undefined' && !user.active && !this.BXIM.messenger.unreadMessage[user.id])
					continue;

				var userId = user.id;
			}
			else
			{
				continue;
			}

			userInList[userId] = true;

			if (item.date)
			{
				item.date = this.formatDate(item.date, this.getDateFormatType('RECENT_TITLE'));
				if (!groups[item.date])
				{
					groups[item.date] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : item.date})
					]}));
				}
			}
			else
			{
				if (!groups['never'])
				{
					groups['never'] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RESENT_NEVER')})
					]}));
				}
			}

			recentList.appendChild(this.drawContactListElement({
				'id': userId,
				'data': user,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params
			}));
			this.BXIM.messenger.recentListIndex.push(userId);
		}

		if (recentList.childNodes.length <= 0)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
		}
		return recentList;
	};

	MessengerCommon.prototype.recentLinesListPrepare = function(params)
	{
		var recentList = document.createDocumentFragment();

		params = typeof(params) == 'object'? params: {};

		if (!this.BXIM.messenger.recentListLoad)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			}));

			this.recentListGetFromServer();
			return recentList;
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		var linesPriority = {};
		var priorityIsActive = false;
		for (var i = 0; i < this.BXIM.messenger.openlines.queue.length; i++)
		{
			linesPriority[this.BXIM.messenger.openlines.queue[i].id] = parseInt(this.BXIM.messenger.openlines.queue[i].priority);

			if (!priorityIsActive && linesPriority[this.BXIM.messenger.openlines.queue[i].id] > 0)
			{
				priorityIsActive = true;
			}
		}

		var chatLinesAssoc = {};


		var limit = this.isMobile()? 49: 999999;
		var recentLinesList = [];
		this.BXIM.messenger.recentListIndex = [];
		var userInList = {};
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (typeof(this.BXIM.messenger.recent[i].userIsChat) == 'undefined')
			{
				this.BXIM.messenger.recent[i].userIsChat = this.BXIM.messenger.recent[i].recipientId.toString().substr(0,4) == 'chat';
			}

			var item = this.BXIM.messenger.recent[i];
			if (i > limit)
			{
				if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
				{
					continue;
				}
			}

			var chatStatus = '';

			if (typeof(item.userIsChat) == 'undefined')
			{
				item.userIsChat = item.recipientId.toString().substr(0,4) == 'chat';
			}
			if (item.userIsChat)
			{
				var chat = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (typeof(chat) == 'undefined' || typeof(chat.name) == 'undefined' || chat.entity_type != 'LINES')
					continue;

				item.chatId = chat.id;
				var dialogId = 'chat'+chat.id;
			}
			else
			{
				continue;
			}

			userInList[dialogId] = true;

			if (priorityIsActive && !chatLinesAssoc[item.chatId])
			{
				var source = chat.entity_id.toString().split('|');
				chatLinesAssoc[item.chatId] = linesPriority[source[1]]? linesPriority[source[1]]: 0;
			}

			var dateStart = chat.entity_data_1.toString().split('|');
			if (typeof(dateStart[6]) != 'undefined')
			{
				dateStart = parseInt(dateStart[6])-(priorityIsActive? chatLinesAssoc[item.chatId]: 0);
				item.dateStart = new Date(dateStart*1000);
			}
			else
			{
				dateStart = typeof(dateStart[5]) != 'undefined'? parseInt(dateStart[5]): 0;
				item.dateStart = new Date(dateStart*1000);
			}

			recentLinesList.push(item);
		}

		recentLinesList.sort(BX.delegate(function (i, ii)
		{
			if (!this.BXIM.messenger.chat[i.chatId])
				return -1;

			if (!this.BXIM.messenger.chat[ii.chatId])
				return 1;

			var i1 = i.dateStart.getTime();
			var i2 = ii.dateStart.getTime();

			if (i1 < i2)
			{
				return -1;
			}
			else if (i1 > i2)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}, this));


		var groups = {};

		var unreadMessage = {};
		for (var userId in this.BXIM.messenger.unreadMessage)
		{
			if (userInList[userId])
				continue;

			unreadMessage[userId] = true;
		}

		for (var userId in unreadMessage)
		{
			if (userId.toString().substr(0,4) == 'chat')
			{
				var user = this.BXIM.messenger.chat[userId.toString().substr(4)];
				if (!user || user.entity_type != 'LINES')
				{
					continue;
				}
			}
			else
			{
				continue;
			}

			if (!groups['30days'])
			{
				groups['30days'] = true;
				recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
					BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : ''})
				]}));
			}

			var item = {text: '', textSenderId: 0, textParams: {}};

			var maxElement = Math.max.apply(Math, this.BXIM.messenger.unreadMessage[userId]);
			if (this.BXIM.messenger.message[maxElement])
			{
				item.text = this.BXIM.messenger.message[maxElement].text;
				item.textSenderId = this.BXIM.messenger.message[maxElement].senderId;
				item.textParams = this.BXIM.messenger.message[maxElement].params;
			}
			recentList.appendChild(this.drawContactListElement({
				'id': userId,
				'data': user,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params,
				'showLastMessage': item.text != ''
			}));
			this.BXIM.messenger.recentListIndex.push(user.id);
		}

		if (this.BXIM.settings.linesNewGroupEnable)
		{
			for (var i = 0; i < recentLinesList.length; i++)
			{
				if (i > limit)
				{
					if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
					{
						continue;
					}
				}

				var item = BX.clone(recentLinesList[i]);
				var chatStatus = '';
				if (item.userIsChat)
				{
					var chat = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
					if (
						typeof(chat) == 'undefined'
						|| typeof(chat.name) == 'undefined'
						|| chat.entity_type != 'LINES'
						|| parseInt(chat.owner) != 0
					)
					{
						continue;
					}

					if (
						item.senderId != 0
						&& this.BXIM.messenger.users[item.senderId]
						&& !this.BXIM.messenger.users[item.senderId].connector
						&& !this.BXIM.messenger.users[item.senderId].bot
						&& !(item.params && item.params.CLASS == 'bx-messenger-content-item-system')
					)
					{
						continue;
					}

					var dialogId = 'chat'+chat.id;
				}
				else
				{
					continue;
				}

				if (!groups['groupNew'])
				{
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title bx-messenger-recent-category-title bx-messenger-recent-category-title-red-2"}, html : BX.message('IM_OL_LIST_NEW')})
					]}));
					groups['groupNew'] = true;
				}

				if (item.date)
				{
					item.date = this.formatDate(item.dateStart, this.getDateFormatType('RECENT_OL_TITLE'));
					if (!groups[item.date])
					{
						groups[item.date] = true;
						recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
							BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : item.date})
						]}));
					}
				}
				else
				{
					if (!groups['never'])
					{
						groups['never'] = true;
						recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
							BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RESENT_NEVER')})
						]}));
					}
				}

				recentList.appendChild(this.drawContactListElement({
					'id': dialogId,
					'data': chat,
					'text': item.text,
					'textSenderId': item.senderId,
					'textParams': item.params
				}));
			}
		}

		var groups = {};

		for (var i = 0; i < recentLinesList.length; i++)
		{
			if (i > limit)
			{
				if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
				{
					continue;
				}
			}

			var item = BX.clone(recentLinesList[i]);
			var chatStatus = '';
			if (item.userIsChat)
			{
				var chat = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (
					typeof(chat) == 'undefined'
					|| typeof(chat.name) == 'undefined'
					|| chat.entity_type != 'LINES'
					|| parseInt(chat.owner) == 0 && this.BXIM.settings.linesNewGroupEnable
				)
				{
					continue;
				}

				if (
					item.senderId != 0
					&& this.BXIM.messenger.users[item.senderId]
					&& !this.BXIM.messenger.users[item.senderId].connector
					&& !this.BXIM.messenger.users[item.senderId].bot
					&& !(item.params && item.params.CLASS == 'bx-messenger-content-item-system')
				)
				{
					continue;
				}

				var dialogId = 'chat'+chat.id;
			}
			else
			{
				continue;
			}

			if (!groups['groupUnanswered'])
			{
				recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
					BX.create("span", {props : { className: "bx-messenger-recent-group-title bx-messenger-recent-category-title bx-messenger-recent-category-title-red"}, html : BX.message('IM_OL_LIST_UNANSWERED')})
				]}));
				groups['groupUnanswered'] = true;
			}

			if (item.date)
			{
				item.date = this.formatDate(item.dateStart, this.getDateFormatType('RECENT_OL_TITLE'));
				if (!groups[item.date])
				{
					groups[item.date] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : item.date})
					]}));
				}
			}
			else
			{
				if (!groups['never'])
				{
					groups['never'] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RESENT_NEVER')})
					]}));
				}
			}

			recentList.appendChild(this.drawContactListElement({
				'id': dialogId,
				'data': chat,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params
			}));
		}

		recentLinesList.sort(function (i, ii)
		{
			var i1 = i.date.getTime();
			var i2 = ii.date.getTime();
			if (i1 > i2)
			{
				return -1;
			}
			else if (i1 < i2)
			{
				return 1;
			}
			else
			{
				if (i > ii)
				{
					return -1;
				}
				else if (i < ii)
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
		});

		var groups = {};
		for (var i = 0; i < recentLinesList.length; i++)
		{
			if (i > limit)
			{
				if (!this.BXIM.messenger.unreadMessage[item.userId] || (this.BXIM.messenger.unreadMessage[item.userId] && this.BXIM.messenger.unreadMessage[item.userId].length == 0))
				{
					continue;
				}
			}

			var item = BX.clone(recentLinesList[i]);
			var chatStatus = '';
			if (item.userIsChat)
			{
				var chat = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (typeof(chat) == 'undefined' || typeof(chat.name) == 'undefined' || chat.entity_type != 'LINES')
					continue;

				if (
					item.senderId == 0
					|| !this.BXIM.messenger.users[item.senderId]
					|| this.BXIM.messenger.users[item.senderId] && (this.BXIM.messenger.users[item.senderId].connector || this.BXIM.messenger.users[item.senderId].bot)
					|| (item.params && item.params.CLASS == 'bx-messenger-content-item-system')
				)
				{
					continue;
				}

				var dialogId = 'chat'+chat.id;
			}
			else
			{
				continue;
			}

			if (!groups['groupAnswered'])
			{
				recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
					BX.create("span", {props : { className: "bx-messenger-recent-group-title bx-messenger-recent-category-title bx-messenger-recent-category-title-green"}, html : BX.message('IM_OL_LIST_ANSWERED')})
				]}));
				groups['groupAnswered'] = true;
			}

			if (item.date)
			{
				item.date = this.formatDate(item.date, this.getDateFormatType('RECENT_OL_TITLE'));
				if (!groups[item.date])
				{
					groups[item.date] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : item.date})
					]}));
				}
			}
			else
			{
				if (!groups['never'])
				{
					groups['never'] = true;
					recentList.appendChild(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RESENT_NEVER')})
					]}));
				}
			}

			recentList.appendChild(this.drawContactListElement({
				'id': dialogId,
				'data': chat,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params
			}));
		}


		if (recentList.childNodes.length <= 0)
		{
			recentList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_OL_EMPTY')
			}));
		}
		return recentList;
	};

	MessengerCommon.prototype.recentListAdd = function(params)
	{
		params.date = params.date? params.date: new Date();

		if (!params.skipDateCheck)
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (
					this.BXIM.messenger.recent[i].userId == params.userId
					&& Math.floor(this.BXIM.messenger.recent[i].date.getTime()/1000) > Math.floor(params.date.getTime()/1000))
				{
					return false;
				}
			}
		}

		var newRecent = [];
		newRecent.push(params);

		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].userId == params.userId)
				params.pinned = this.BXIM.messenger.recent[i].pinned === true;
			else
				newRecent.push(this.BXIM.messenger.recent[i]);
		}

		this.BXIM.messenger.recent = newRecent;

		if (!params.skipRedraw && this.BXIM.messenger.recentList)
		{
			if (this.isMobile())
			{
				clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
				this.BXIM.messenger.redrawRecentListTimeout = setTimeout(BX.delegate(function(){
					this.recentListRedraw();
				}, this), 300);
			}
			else
			{
				this.recentListRedraw();
			}
		}
	};

	MessengerCommon.prototype.inRecentList = function(dialogId)
	{
		if (!dialogId)
			return false;

		var dialogFound = false;
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].userId == dialogId)
			{
				dialogFound = true;
				break;
			}
		}

		return dialogFound;
	};

	MessengerCommon.prototype.recentListHide = function(dialogId, sendAjax)
	{
		if (!dialogId)
			return false;

		var newRecent = [];
		var itemDelete = false;
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (!itemDelete && this.BXIM.messenger.recent[i].userId == dialogId)
			{
				itemDelete = true;
				continue;
			}
			newRecent.push(this.BXIM.messenger.recent[i]);
		}

		this.BXIM.messenger.recent = newRecent;
		if (this.BXIM.messenger.recentList)
			this.recentListRedraw();

		if (!this.isMobile())
			BX.localStorage.set('mrlr', dialogId, 5);

		sendAjax = sendAjax != false;

		if (sendAjax)
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?RECENT_HIDE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_RECENT_HIDE' : 'Y', 'DIALOG_ID' : dialogId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}

		this.readMessage(dialogId, sendAjax, sendAjax);

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

	MessengerCommon.prototype.recentListElementUpdate = function(userId, messageId, messageText)
	{
		if (userId.toString().substr(0,4) == 'chat')
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (this.BXIM.messenger.recent[i].userIsChat && this.BXIM.messenger.recent[i].recipientId == userId)
				{
					if (this.BXIM.messenger.recent[i].id == messageId)
					{
						this.BXIM.messenger.recent[i].text = messageText;
					}
					break;
				}
			}
		}
		else
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (!this.BXIM.messenger.recent[i].userIsChat && this.BXIM.messenger.recent[i].userId == userId)
				{
					if (this.BXIM.messenger.recent[i].id == messageId)
					{
						this.BXIM.messenger.recent[i].text = messageText;
					}
					break;
				}
			}
		}
	}

	MessengerCommon.prototype.recentListElementToTop = function(userId)
	{
		var userFound = false;
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].userId == userId)
			{
				userFound = true;
				this.BXIM.messenger.recent[i].date = new Date();

				break;
			}
		}

		if (!userFound)
		{
			var messageText = '';
			var lastMessage = this.getLastMessageInDialog(userId);
			if (lastMessage)
			{
				if (lastMessage.text)
				{
					messageText = lastMessage.text;
				}
				else if (lastMessage.params && lastMessage.params.FILE_ID && lastMessage.params.FILE_ID.length > 1)
				{
					messageText = '['+BX.message('IM_F_FILE')+']';
				}
				else if (lastMessage.params && lastMessage.params.ATTACH && lastMessage.params.ATTACH.length > 1)
				{
					item.text = '['+BX.message('IM_F_ATTACH')+']';
				}
			}

			if (!messageText)
			{
				var userParam = this.getUserParam(userId);
				if (userParam.type == 'chat')
				{
					messageText = BX.message('IM_CL_CHAT_2');
				}
				else if (userParam.type == 'open')
				{
					messageText = BX.message('IM_CL_OPEN_CHAT');
				}
				else if(userParam.type == 'call')
				{
					messageText = BX.message('IM_CL_PHONE');
				}
				else if(userParam.type == 'lines')
				{
					messageText = BX.message('IM_CL_LINES');
				}
				else
				{
					messageText = BX.util.htmlspecialcharsback(this.getUserPosition(this.BXIM.messenger.users[userId], true));
				}
			}

			this.BXIM.messenger.recent.push({
				'id': 'tempSort'+(+new Date()),
				'date': new Date(),
				'skipDateCheck': true,
				'recipientId': userId,
				'senderId': userId,
				'text': BX.MessengerCommon.prepareText(messageText, true),
				'userId': userId,
				'params': {}
			});
		}

		if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
			this.recentListRedraw();

		if (!this.isMobile())
			BX.localStorage.set('mrlr', userId, 5);
	};

	MessengerCommon.prototype.recentListElementPin = function(dialogId, active)
	{
		var userFound = false;
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].userId == dialogId)
			{
				userFound = true;
				if (this.BXIM.messenger.recent[i].pinned == active)
				{
					return true;
				}

				this.BXIM.messenger.recent[i].pinned = active;

				break;
			}
		}

		if (userFound && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
			this.recentListRedraw();

		return true;
	};

	MessengerCommon.prototype.recentListGetSortIndex = function()
	{
		var sortIndex = {};
		var tmpIndex = 0;

		if (this.BXIM.messenger.recent.length <= 0)
		{
			this.recentListGetFromServer();
		}

		for (var item = 0; item < this.BXIM.messenger.recent.length; item++)
		{
			tmpIndex =  this.BXIM.messenger.recent.length-item;
			sortIndex[this.BXIM.messenger.recent[item].userId] = tmpIndex;
		}

		return sortIndex;
	}

	MessengerCommon.prototype.recentListGetFromServer = function()
	{
		if (this.BXIM.messenger.recentListLoad)
			return false;

		this.BXIM.messenger.recentListLoad = true;
		BX.ajax({
			url: this.BXIM.pathToAjax+'?RECENT_LIST&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_RECENT_LIST' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					this.BXIM.messenger.recent = [];
					for (var i in data.RECENT)
					{
						data.RECENT[i].date = new Date(data.RECENT[i].date);
						this.BXIM.messenger.recent.push(data.RECENT[i]);
					}

					var arRecent = false;
					for(var i in this.BXIM.messenger.unreadMessage)
					{
						for (var k = 0; k < this.BXIM.messenger.unreadMessage[i].length; k++)
						{
							if (!arRecent || this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]] && arRecent.SEND_DATE.getTime() <= this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].date.getTime())
							{
								arRecent = {
									'ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].id,
									'SEND_DATE': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].date,
									'RECIPIENT_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].recipientId,
									'SENDER_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].senderId,
									'USER_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].senderId,
									'SEND_MESSAGE': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].text,
									'PARAMS': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].params
								};
							}
						}
					}
					if (arRecent)
					{
						this.recentListAdd({
							'userId': arRecent.RECIPIENT_ID.toString().substr(0,4) == 'chat'? arRecent.RECIPIENT_ID: arRecent.USER_ID,
							'id': arRecent.ID,
							'date': arRecent.SEND_DATE,
							'recipientId': arRecent.RECIPIENT_ID,
							'senderId': arRecent.SENDER_ID,
							'text': arRecent.SEND_MESSAGE,
							'userIsChat': arRecent.RECIPIENT_ID.toString().substr(0,4) == 'chat',
							'params': arRecent.PARAMS
						}, true);
					}

					for (var i in data.CHAT)
					{
						if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
							data.CHAT[i].fake = true;
						else if (!this.BXIM.messenger.chat[i])
							data.CHAT[i].fake = true;

						data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
						this.BXIM.messenger.chat[i] = data.CHAT[i];
					}

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.BXIM.messenger.users[i] = data.USERS[i];
					}

					if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
						this.recentListRedraw();

					this.BXIM.messenger.smile = data.SMILE;
					this.BXIM.messenger.smileSet = data.SMILE_SET;

					this.BXIM.settingsNotifyBlocked = data.NOTIFY_BLOCKED;
					if (!this.isMobile())
						this.BXIM.messenger.dialogStatusRedraw();

					if (this.BXIM.messenger.recent.length == 0)
					{
						this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
						this.BXIM.messenger.popupContactListElementsWrap.appendChild(this.chatListPrepare());
					}
				}
				else
				{
					this.BXIM.messenger.recentListLoad = false;
					if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(this.recentListGetFromServer, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.isDesktop() || this.isMobile())
						{
							setTimeout(BX.delegate(this.recentListGetFromServer, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.sendAjaxTry = 0;
				this.BXIM.messenger.recentListLoad = false;
			}, this)
		});
	};

	MessengerCommon.prototype.drawContactListElement = function(params)
	{
		if (!params || !params.id)
			return null;

		params.userIsChat = params.id.toString().substr(0,4) == 'chat';
		params.userIsQueue = params.id.toString().substr(0,5) == 'queue';
		params.userIsStructure = params.id.toString().substr(0,9) == 'structure';
		params.extraClass = params.extraClass || '';
		params.showLastMessage = params.showLastMessage === false? false: true;
		params.showCounter = params.showCounter === false? false: true;
		params.data = params.data? params.data: {};

		var chatStatus = '';
		var newMessage = '';
		var newMessageCount = '';
		var writingMessage = '';

		if (params.showCounter)
		{
			if (this.BXIM.messenger.unreadMessage[params.id] && this.BXIM.messenger.unreadMessage[params.id].length>0)
			{
				newMessage = 'bx-messenger-cl-status-new-message';
				newMessageCount = '<span class="bx-messenger-cl-count-digit">'+(this.BXIM.messenger.unreadMessage[params.id].length<100? this.BXIM.messenger.unreadMessage[params.id].length: '99+')+'</span>';
			}
			if (this.countWriting(params.id))
				writingMessage = 'bx-messenger-cl-status-writing';
		}

		if (params.userIsQueue)
		{
			params.data.avatar = '';
			params.data.color = this.BXIM.messenger.users[this.BXIM.userId].color;
		}
		else if (params.userIsStructure)
		{
			params.data.avatar = '';
			params.data.color = this.BXIM.messenger.users[this.BXIM.userId].color;
		}

		if (!params.data.avatar)
			params.data.avatar = this.BXIM.pathToBlankImage;

		var avatarId = '';
		var avatarLink = params.data.avatar;
		var mobileItemActive = '';
		if (this.isMobile())
		{
			if (this.BXIM.messenger.currentTab == params.id)
			{
				mobileItemActive = 'bx-messenger-cl-item-active ';
			}
			var lazyUserId = 'mobile-rc-avatar-id-'+params.data.id;
			avatarId = 'id="'+lazyUserId+'" data-src="'+params.data.avatar+'"';
			avatarLink = this.BXIM.pathToBlankImage;
			BitrixMobile.LazyLoad.registerImage(lazyUserId, function(obj){
				return !obj.node.parentNode.parentNode.classList.contains('bx-messenger-hide') ||
					obj.node.parentNode.parentNode.parentNode.classList.contains('bx-messenger-chatlist-show-all');
			});
		}

		var description = '';
		var showCrm = false;
		if (this.BXIM.settings.viewLastMessage && params.showLastMessage)
		{
			if (this.BXIM.messenger.message[params.id] && this.BXIM.messenger.message[params.id].text)
			{
				params.text = this.BXIM.messenger.message[params.id].text;
			}

			var directionIcon = '';
			if (params.textSenderId == this.BXIM.userId)
				directionIcon = '<span class="bx-messenger-cl-user-reply"></span>';

			params.text = this.purifyText(params.text, params.textParams);

			description = directionIcon+''+params.text;
		}
		else
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
					description = BX.message('IM_CL_OPEN_CHAT');
				}
				else
				{
					description = BX.message('IM_CL_CHAT_2');
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
				description = this.getUserPosition(this.BXIM.messenger.users[params.id], true);
			}
		}

		if (params.userIsChat)
		{
			if (params.data.type == 'lines')
			{
				var session = this.linesGetSession(this.BXIM.messenger.chat[params.id.substr(4)]);
				showCrm = session.crm == 'Y';
				chatStatus += " bx-messenger-cl-avatar-"+this.linesGetSource(this.BXIM.messenger.chat[params.id.substr(4)]);
			}
			else
			{
				chatStatus = "bx-messenger-cl-item-chat-"+params.data.type;
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

		var avatarColor = this.isBlankAvatar(params.data.avatar)? 'style="background-color: '+params.data.color+'"': '';
		var chatHideAvatar = params.userIsChat && avatarColor? 'bx-messenger-cl-avatar-status-hide': '';
		var userName = params.data.name;
		if (!params.userIsChat && !params.userIsQueue && !params.userIsStructure && this.BXIM.userId == params.data.id)
		{
			userName = userName+' (<b><i>'+BX.message('IM_YOU')+'</i></b>)';
		}

		var classAvatar = '';
		var className = "bx-messenger-cl-item  bx-messenger-cl-id-"+(params.userIsChat? 'chat':'')+(params.userIsQueue? 'queue':'')+params.data.id+" "+mobileItemActive;
		if (params.userIsChat)
		{
			classAvatar = 'bx-messenger-cl-avatar-'+params.data.type+' '+(this.BXIM.messenger.generalChatId == params.data.id? " bx-messenger-cl-item-chat-general": "");
			className += "bx-messenger-cl-item-chat "+newMessage+" "+writingMessage+" "+chatStatus+" "+(this.BXIM.messenger.generalChatId == params.data.id? "bx-messenger-cl-item-chat-general": "");
		}
		else if (params.userIsQueue)
		{
			className += chatStatus;
		}
		else if (params.userIsStructure)
		{
			className += chatStatus;
		}
		else
		{
			className += "bx-messenger-cl-status-" +this.getUserStatus(this.BXIM.messenger.users[params.data.id])+ " " +newMessage+" "+writingMessage;
		}
		className += " "+params.extraClass;

		if (params.pinned)
		{
			className += " bx-messenger-cl-item-pinned";
		}

		return BX.create("span", {
			props : { className: className },
			attrs : { 'data-userId' : params.id, 'data-name' : BX.util.htmlspecialcharsback(params.data.name), 'data-status' : this.getUserStatus(this.BXIM.messenger.users[params.data.id]), 'data-avatar' : params.data.avatar, 'data-userIsChat' : params.userIsChat, 'data-isPinned' : params.pinned, 'data-userIsQueue' : params.userIsQueue },
			html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
					'<span title="'+params.data.name+'" class="bx-messenger-cl-avatar '+classAvatar+' '+chatHideAvatar+'">' +
						'<img class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(params.data.avatar)? " bx-messenger-cl-avatar-img-default": "")+'" src="'+avatarLink+'" '+avatarId+' '+avatarColor+'>' +
						(showCrm? '<span class="bx-messenger-cl-crm"></span>':'') +
						(!params.userIsQueue && !params.userIsStructure? '<span class="bx-messenger-cl-status"></span>':'') +
						/*'<span class="bx-messenger-loader">'+
							'<span class="bx-messenger-loader-default bx-messenger-loader-first"></span>'+
							'<span class="bx-messenger-loader-default bx-messenger-loader-second"></span>'+
							'<span class="bx-messenger-loader-mask"></span>'+
						'</span>'+*/
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
		this.BXIM.messenger.contactList = false;

		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

		if (!this.isMobile() && this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
		{
			this.BXIM.messenger.popupPopupMenu.close();
		}

		if (this.BXIM.messenger.popupContactListElementsWrap)
		{
			BX.removeClass(this.BXIM.messenger.popupContactListElementsWrap, 'bx-messenger-recent-lines-wrap');

			this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
			this.BXIM.messenger.popupContactListElementsWrap.appendChild(this.chatListPrepare(params));
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
		var viewOnlyBusiness =  typeof(params.viewOnlyBusiness) != 'undefined'? params.viewOnlyBusiness: false;
		var extraEnable =  typeof(params.extra) != 'undefined'? params.extra: true;
		var viewOffline =  typeof(params.viewOffline) != 'undefined'? params.viewOffline: activeSearch || !this.BXIM.settings? true: this.BXIM.settings.viewOffline;
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

		if (typeof(callback.empty) != 'function')
		{
			callback.empty = function(){}
		}

		if (!this.BXIM.messenger.contactListLoad)
		{
			chatList.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
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
			category.push({'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_PRIVATE')});

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
			category.push({'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_PRIVATE')});
			category.push({'id': 'bot', 'name': BX.message('IM_CTL_CHAT_BOT'), 'title': '', 'more': BX.message('IM_CL_MORE_BOT')});
			category.push({'id': 'open', 'name': BX.message('IM_CTL_CHAT_OPEN'), 'title': BX.message('IM_CL_CREATE_OPEN'), 'more': BX.message('IM_CL_MORE_OPEN'), skip: !this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet});
			category.push({'id': 'chat', 'name': BX.message('IM_CTL_CHAT_CHAT'), 'title': BX.message('IM_CL_CREATE_CHAT'), 'more': BX.message('IM_CL_MORE_CHAT')});
			category.push({'id': 'lines', 'name': BX.message('IM_CTL_CHAT_LINES'), 'title': '', 'more': BX.message('IM_CL_MORE_LINES')});
			category.push({'id': 'call', 'name': BX.message('IM_CTL_CHAT_CALL'), 'title': '', 'more': BX.message('IM_CL_MORE_CALL'),  skip: !this.BXIM.webrtc.phoneEnabled});
			category.push({'id': 'ol', 'name': BX.message('IM_CTL_CHAT_OL'), 'title': '', 'more': BX.message('IM_CTL_CHAT_OL'), skip: this.BXIM.userExtranet});
			category.push({'id': 'extranet', 'name': BX.message('IM_CTL_CHAT_EXTRANET'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_EXTRANET')});
			category.push({'id': 'structure', 'name': this.BXIM.bitrixIntranet? BX.message('IM_CTL_CHAT_STRUCTURE'): BX.message('IM_CL_GROUP'), 'title': '', 'more': this.BXIM.bitrixIntranet? BX.message('IM_CL_MORE_STRUCTURE'): BX.message('IM_CL_MORE_GROUP'), skip: !showStructureBlock});
			category.push({'id': 'blocked', 'name': BX.message('IM_CTL_CHAT_BLOCKED'), 'title': '', 'more': BX.message('IM_CL_MORE_EXTRANET')});
		}
		else
		{
			category.push({'id': 'open', 'name': BX.message('IM_CTL_CHAT_OPEN'), 'title': BX.message('IM_CL_CREATE_OPEN'), 'more': BX.message('IM_CL_MORE_OPEN'), skip: !this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet});
			category.push({'id': 'chat', 'name': BX.message('IM_CTL_CHAT_CHAT'), 'title': BX.message('IM_CL_CREATE_CHAT'), 'more': BX.message('IM_CL_MORE_CHAT')});
			category.push({'id': 'lines', 'name': BX.message('IM_CTL_CHAT_LINES'), 'title': '', 'more': BX.message('IM_CL_MORE_LINES')});
			category.push({'id': 'call', 'name': BX.message('IM_CTL_CHAT_CALL'), 'title': '', 'more': BX.message('IM_CL_MORE_CALL'),  skip: !this.BXIM.webrtc.phoneEnabled});
			category.push({'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_PRIVATE')});
			category.push({'id': 'bot', 'name': BX.message('IM_CTL_CHAT_BOT'), 'title': '', 'more': BX.message('IM_CL_MORE_BOT')});
			category.push({'id': 'ol', 'name': BX.message('IM_CTL_CHAT_OL'), 'title': '', 'more': BX.message('IM_CTL_CHAT_OL'), skip: this.BXIM.userExtranet});
			category.push({'id': 'extranet', 'name': BX.message('IM_CTL_CHAT_EXTRANET'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_EXTRANET')});
			category.push({'id': 'structure', 'name': this.BXIM.bitrixIntranet? BX.message('IM_CTL_CHAT_STRUCTURE'): BX.message('IM_CTL_CHAT_GROUP'), 'title': '', 'more': this.BXIM.bitrixIntranet? BX.message('IM_CL_MORE_STRUCTURE'): BX.message('IM_CL_MORE_GROUP'), skip: !showStructureBlock});
			category.push({'id': 'blocked', 'name': BX.message('IM_CTL_CHAT_BLOCKED'), 'title': '', 'more': BX.message('IM_CL_MORE_EXTRANET')});
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

					if (typeof(this.BXIM.messenger.users[userId].active) != 'undefined' && !this.BXIM.messenger.users[userId].active)
						continue;

					if (viewOnlyBusiness && this.BXIM.messenger.businessUsers && !this.BXIM.messenger.users[userId].bot && this.BXIM.messenger.businessUsers.indexOf(userId.toString()) == -1)
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
							continue;

						if (!this.BXIM.messenger.bot[userId] || this.BXIM.messenger.bot[userId].type != 'network')
							continue;
					}
					else if (category[i].id == 'bot')
					{
						if (!this.BXIM.messenger.users[userId].bot || !this.BXIM.messenger.bot[userId])
							continue;

						if (this.BXIM.messenger.bot[userId] && this.BXIM.messenger.bot[userId].type == 'network')
							continue;

						if (this.BXIM.messenger.popupChatDialogDestType == 'CALL_INVITE_USER')
						{
							continue;
						}

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
							else if (this.BXIM.messenger.bot[userId].type == 'network')
							{
								continue;
							}
						}
						else
						{
							if (this.BXIM.messenger.bot[userId].type == 'network' || this.BXIM.messenger.bot[userId].type == 'openline')
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
						var userSearchByName = user.name.toLowerCase() + (user.search_mark? " " + user.search_mark: "");
						var userSearchByPosition = user.work_position? (" " + user.work_position).toLowerCase(): "";
						var skipUser = true;

						if (!sortIndex[userId])
						{
							sortIndex[userId] = 0;
						}
						for (var s = 0; s < arSearch.length; s++)
						{
							if (userSearchByName.indexOf(arSearch[s].toLowerCase()) >= 0)
							{
								sortIndex[userId] += 100+arSearch[s].length;
								skipUser = false;
							}
							if (userSearchByPosition.indexOf(arSearch[s].toLowerCase()) >= 0)
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

					if (this.BXIM.messenger.chat[chatId].type != category[i].id)
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
							if (this.BXIM.messenger.chat[chatId].name.toLowerCase().indexOf(arSearch[s].toLowerCase()) >= 0)
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

				for (var queueId = 0; queueId < this.BXIM.messenger.openlines.queue.length; queueId++)
				{
					if (activeSearch)
					{
						var skipItem = true;
						for (var s = 0; s < arSearch.length; s++)
						{
							if (this.BXIM.messenger.openlines.queue[queueId].name.toLowerCase().indexOf(arSearch[s].toLowerCase()) >= 0)
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
							if (this.BXIM.messenger.groups[groupId].name.toLowerCase().indexOf(arSearch[s].toLowerCase()) >= 0)
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
						BX.create("span", {props : { className: "bx-messenger-chatlist-search-button"}, html: this.BXIM.bitrixIntranet? BX.message('IM_SEARCH_B24'): BX.message('IM_SEARCH_SITE')})
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

					categoryItems.push(this.drawContactListElement({
						'id': user.id,
						'data': user,
						'showLastMessage': false,
						'showCounter': extraEnable,
						'extraClass': isShown? '': 'bx-messenger-hide'
					}));
				}
				else if (category[i].id == 'chat' || category[i].id == 'open' || category[i].id == 'call' || category[i].id == 'lines')
				{
					var chat = groupElements[i][j];
					categoryItems.push(this.drawContactListElement({
						'id': 'chat'+chat.id,
						'data': chat,
						'showLastMessage': false,
						'showCounter': extraEnable,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					}));
				}
				else if (category[i].id == 'olQueue' || category[i].id == 'viQueue')
				{
					var queue = groupElements[i][j];
					queue.type = category[i].id;
					categoryItems.push(this.drawContactListElement({
						'id': 'queue'+queue.id,
						'data': queue,
						'showLastMessage': false,
						'showCounter': false,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					}));
				}
				else if (category[i].id == 'structure')
				{
					var structure = groupElements[i][j];
					categoryItems.push(this.drawContactListElement({
						'id': 'structure'+structure.id,
						'data': structure,
						'showLastMessage': false,
						'showCounter': false,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					}));
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
						BX.create("span", {props : { className: "bx-messenger-chatlist-search-button"}, html: BX.message('IM_SEARCH_B24')})
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
				if (this.BXIM.userExtranet && !commandList[i].extranet)
					continue;

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
				if (commandList[i].command == '/>>')
				{
					commandList[i].command = '>>';
				}
				commandList[i].type = 'item';
				list.push(commandList[i]);
			}
		}
		return list;
	}

	MessengerCommon.prototype.drawMessage = function(dialogId, message, scroll, appendTop)
	{
		if (typeof(message) != 'object' || this.BXIM.messenger.popupMessenger == null)
			return false;

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

		var isChat = false;
		var isGeneralChat = false;
		var edited = message.params && message.params.IS_EDITED == 'Y';
		var deleted = message.params && message.params.IS_DELETED == 'Y';
		var messageText = deleted? BX.message('IM_M_DELETED'): message.text;
		var temp = message.id.toString().indexOf('temp') == 0;
		var retry = temp && message.retry;
		var system = message.senderId == 0;
		var likeEnable = this.BXIM.ppServerStatus;
		var withAppsMenu = message.params && message.params.MENU;

		if (temp)
		{
			messageText = this.decodeBbCode(messageText);

			messageText = messageText.replace(/(^https|^http|[^"]https|[^"]http):\/\/([\S]+)\.(jpg|jpeg|png|gif)(\?[\S]+)?/ig, function(whole)
			{
				return '<span class="bx-messenger-file-image"><span class="bx-messenger-file-image-src"><img src="'+whole+'" class="bx-messenger-file-image-text"></span></span>';
			});
		}

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
			else if (!isChat && this.BXIM.messenger.bot[message.recipientId] && this.BXIM.messenger.bot[message.recipientId].type == 'network')
			{
				likeEnable = false;
			}
		}
		else
		{
			if (message.senderId == this.BXIM.userId)
			{
				if (this.BXIM.messenger.popupMessengerLastMessage > 0 && this.BXIM.messenger.message[this.BXIM.messenger.popupMessengerLastMessage] && this.BXIM.messenger.message[this.BXIM.messenger.popupMessengerLastMessage].recipientId == this.BXIM.messenger.currentTab)
				{
					if (this.BXIM.messenger.popupMessengerLastMessage < message.id)
					{
						this.BXIM.messenger.popupMessengerLastMessage = message.id;
					}
				}
				else
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
			else if (!this.BXIM.messenger.openChatFlag && this.BXIM.messenger.bot[this.BXIM.messenger.currentTab] && this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type == 'network')
			{
				likeEnable = false;
			}
		}

		if (typeof(message.params) != 'object')
		{
			message.params = {};
		}

		if (message.params.DATE_TEXT)
		{
			for (var i = 0; i < message.params.DATE_TEXT.length; i++)
			{
				messageText = messageText.split(message.params.DATE_TEXT[i]).join('<span class="bx-messenger-ajax bx-messenger-ajax-black" data-entity="date" data-messageId="'+message.id+'" data-ts="'+message.params.DATE_TS[i]+'">'+message.params.DATE_TEXT[i]+'</span>');
			}
		}

		var likeCount = likeEnable && typeof(message.params.LIKE) == "object" && message.params.LIKE.length > 0? message.params.LIKE.length: '';
		var iLikeThis = likeEnable && typeof(message.params.LIKE) == "object" && BX.util.in_array(this.BXIM.userId, message.params.LIKE);

		var filesNode = this.diskDrawFiles(message.chatId, message.params.FILE_ID);
		if (filesNode.length > 0)
		{
			filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(messageText != ''? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
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

		if (message.params.LINK_ACTIVE && message.params.LINK_ACTIVE.length > 0 && message.params.LINK_ACTIVE.indexOf(this.BXIM.userId.toString()) < 0)
		{
			messageText = messageText.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/ig, '$2');
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

		var keyboardNode = null;
		if (showKeyboard && message.params.KEYBOARD)
		{
			keyboardNode = this.drawKeyboard(message.recipientId, message.id, message.params.KEYBOARD);
		}

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
		var messageUser = this.BXIM.messenger.users[message.senderId];
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
				var checkImages = messageText.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1').replace(/<\/?[^>]+>/gi, ' ').replace(/(https|http):\/\/([\S]+)\.(jpg|jpeg|png|gif)(\?[\S]+)?/ig, function(whole) {return '';}).trim();
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

			textNode = BX.create("span", {
				props : { className : "bx-messenger-message"},
				attrs: {'id' : 'im-message-'+message.id},
				html: this.prepareText(messageText, false, true, true, (!this.BXIM.messenger.openChatFlag || message.senderId == this.BXIM.userId? false: (this.BXIM.messenger.users[this.BXIM.userId].name)))}
			);
		}
		else
		{
			textNode = BX.create("span", {
				props : { className : "bx-messenger-message"},
				attrs: {'id' : 'im-message-'+message.id},
				children: [messageText]}
			);
		}

		if (!skipAddMessage)
		{
			if (lastMessage)
				messageId = lastMessage.getAttribute('data-messageId');

			if (system)
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'system', 'data-senderId' : "0", 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-id-"+message.id+" bx-messenger-content-item-notice "+extraClass}, children: [
					extraNode,
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")}, children : [
						!isGeneralChat? []: BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"+(withAppsMenu? ' bx-messenger-content-item-menu-with-apps': '')}}),
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
								BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}}),
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
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")}, children : [
						BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"+(withAppsMenu? ' bx-messenger-content-item-menu-with-apps': '')}}),
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
								BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}}),
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
					BX.create("span", { props : { className : "bx-messenger-content-item-content"+(messageWithoutPadding? " bx-messenger-content-item-content-without-padding": "")+(messageOnlyRichLink && !deleted? " bx-messenger-content-item-content-rich-link": "")+(deleted || edited?" bx-messenger-message-edited": "")}, children : [
						BX.create("span", { attrs: {title : (withAppsMenu? BX.message('IM_M_MENU_APP_EXISTS')+' ': '')+BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"+(withAppsMenu? ' bx-messenger-content-item-menu-with-apps': '')}}),
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
								BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}}),
								this.BXIM.messenger.openChatFlag || messageUser.bot? BX.create("span", { props : { className : "bx-messenger-content-item-avatar-name"}, attrs : { title: BX.util.htmlspecialcharsback(messageUser.name)}, html: messageUser.firstName? messageUser.firstName: messageUser.name.split(" ")[0]}): null
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

		var replyChild = [];
		var userIds = this.BXIM.messenger.message[messageId].params.CHAT_USER || [];

		var count = 0;
		for (var i = userIds.length - 1; i >= 0; i--)
		{
			if (
				!this.BXIM.messenger.users[userIds[i]]
				|| !this.BXIM.messenger.userInChat[currentChatId]
				|| this.BXIM.messenger.userInChat[currentChatId].indexOf(userIds[i]) == -1 && this.BXIM.messenger.userInChat[currentChatId].indexOf(userIds[i].toString()) == -1
			)
			{
				continue;
			}

			var avatarColor = this.isBlankAvatar(this.BXIM.messenger.users[userIds[i]].avatar)? this.BXIM.messenger.users[userIds[i]].color: 'transparent';
			replyChild.push(
				BX.create("span", { props : { className : "bx-messenger-panel-chat-user" }, children: [
					BX.create("span", { props : { className : "bx-notifier-popup-avatar" }, children: [
						BX.create("img", { props : { className : "bx-notifier-popup-avatar-img"+(this.isBlankAvatar(this.BXIM.messenger.users[userIds[i]].avatar)? " bx-notifier-popup-avatar-img-default": "") }, attrs: { src: this.BXIM.messenger.users[userIds[i]].avatar}, style: {backgroundColor: avatarColor}})
					]})
				]})
			);
			count++;
			if (count == 5)
			{
				break;
			}
		}

		var lastMessageDate = this.BXIM.messenger.message[messageId].params.CHAT_LAST_DATE? new Date(this.BXIM.messenger.message[messageId].params.CHAT_LAST_DATE): '';
		var messagesCount = this.BXIM.messenger.message[messageId].params.CHAT_MESSAGE || 0;

		node = BX.create("div", { props : { className : "bx-messenger-content-reply" }, attrs: {id: 'im-message-content-reply-'+messageId, 'data-messageId': messageId, 'data-chatid': chatId}, children: [
			BX.create("span", { props : { className : "bx-messenger-content-reply-block" }, children: [
				BX.create("span", { props : { className : "bx-messenger-content-reply-comment" }, children: [
					BX.create("span", { props : { className : "bx-messenger-content-reply-answer" }, events: {click: BX.delegate(function(){
						this.joinParentChat(BX.proxy_context.getAttribute('data-messageId'), BX.proxy_context.getAttribute('data-chatId'));
					}, this)}, attrs: {'data-messageId': messageId, 'data-chatId': chatId}, html: messagesCount+' '+this.getMessagePlural('IM_R_COMMENT', messagesCount)}),
					BX.create("span", { props : { className : "bx-messenger-content-reply-date" }, html: lastMessageDate? ', '+this.formatDate(lastMessageDate): ''}),
				]}),
				BX.create("span", { props : { className : "bx-messenger-content-reply-users" }, children: replyChild}),
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
			else if (parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+86400 < ((new Date).getTime()/1000))
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
		BX.MessengerTimer.start('progressMessage', messageId, 5000, function(id) {
			var element = BX('im-message-'+id);
			if (!element)
				return false;

			BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-start');
		});

		element.parentNode.parentNode.parentNode.previousSibling.innerHTML = '';

		var isDelivered = true;
		if (
			this.BXIM.messenger.message[messageId]
			&& this.BXIM.messenger.message[messageId].params
			&& (
				this.BXIM.messenger.message[messageId].params.SENDING == 'Y' && parseInt(this.BXIM.messenger.message[messageId].params.SENDING_TS)+86400 < (new Date()).getTime()/1000
				|| this.BXIM.messenger.message[messageId].params.IS_DELIVERED == 'N'
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
				BX.MessengerTimer.stop('progressMessage', messageId, true);
			}
		}
		else if (typeof (button) == 'object' || button === true)
		{
			if (this.BXIM.messenger.message[messageId])
			{
				this.BXIM.messenger.errorMessage[this.BXIM.messenger.currentTab] = true;
				BX.addClass(element.parentNode.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
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

		if (this.BXIM.messenger.popupMessenger != null && this.MobileActionEqual('RECENT', 'DIALOG'))
		{
			if (this.BXIM.messenger.writingList[userId] || dialogId && this.countWriting(dialogId) > 0)
			{
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.addClass(elements[i], 'bx-messenger-cl-status-writing');
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

					var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
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
				delete this.BXIM.messenger.unreadMessage[chatId];
				delete this.BXIM.messenger.showMessage[chatId];

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
				delete this.BXIM.messenger.unreadMessage[chatId];
			}

			this.recentListHide('chat'+chatId, false);
			this.userListRedraw();
		}
		else
		{
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

						if (this.BXIM.messenger.chat[data.CHAT_ID].type != 'open' || this.BXIM.messenger.users[this.BXIM.userId].extranet)
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
					}
				}, this)
			});
		}
	};

	MessengerCommon.prototype.dialogCloseCurrent = function(close)
	{
		var item = BX.findChildByClassName(this.BXIM.messenger.popupContactListWrap, "bx-messenger-cl-item");
		if (item && !close)
		{
			this.BXIM.messenger.openMessenger(item.getAttribute('data-userId'));
		}
		else
		{
			this.BXIM.messenger.currentTab = 0;
			this.BXIM.messenger.openChatFlag = false;
			this.BXIM.messenger.openCallFlag = false;
			this.BXIM.messenger.openLinesFlag = false;
			this.BXIM.messenger.extraClose();
		}
	}

	/* Section: Pull Events */
	MessengerCommon.prototype.pullEvent = function()
	{
		var pullHandler = BX.delegate(function(command,params,extra)
		{
			if (!this.isMobile() && !this.BXIM.checkRevision(extra.im_revision))
			{
				return false;
			}

			if (command == 'generalChatId')
			{
				this.BXIM.messenger.generalChatId = params.id;
			}
			else if (command == 'updateSettings')
			{
				for (var i in params)
				{
					this.BXIM.settings[i] = params[i];
				}
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
			else if (command == 'desktopOffline')
			{
				this.BXIM.desktopStatus = false;
			}
			else if (command == 'desktopOnline')
			{
				this.BXIM.desktopStatus = true;
			}
			else if (command == 'readMessage')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.readMessage(params.userId, false, false, true);
			}
			else if (command == 'readMessageChat')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.readMessage('chat'+params.chatId, false, false, true);
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
			}
			else if (command == 'unreadMessageOpponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
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
			else if (command == 'addBot' || command == 'updateBot')
			{
				if (this.BXIM.userExtranet)
					return false;

				this.BXIM.messenger.bot[params.bot.id] = params.bot;

				params.user.last_activity_date = new Date(params.user.last_activity_date);
				params.user.mobile_last_date = new Date(params.user.mobile_last_date);
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
			else if (command == 'updateUser')
			{
				params.user.last_activity_date = new Date(params.user.last_activity_date);
				params.user.mobile_last_date = new Date(params.user.mobile_last_date);
				params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
				params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

				this.BXIM.messenger.users[params.user.id] = params.user;
				this.BXIM.messenger.redrawChatHeader();
			}
			else if (command == 'deleteBot')
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
			else if (command == 'chatMuteNotify')
			{
				this.muteMessageChat(params.dialogId, params.mute, false);
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

				var dialogId = params.message.senderId;
				if (params.message.recipientId.toString().substr(0, 4) == 'chat')
				{
					dialogId = params.message.recipientId;
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

				if (this.isBot(params.message.senderId) && !params.deferred && this.BXIM.messenger.showMessage[dialogId] && this.BXIM.messenger.showMessage[dialogId].length)
				{
					var bot = this.BXIM.messenger.bot[params.message.senderId];
					if (bot.type == 'human')
					{
						if (params.chat[dialogId] && params.chat[dialogId].entity_type == 'LINES')
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

				var data = {};
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

				data.MESSAGE[params.message.id] = params.message;

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
					if (
						this.BXIM.messenger.sendMessageFlag > 0 && params.message.system != 'Y'
						|| this.BXIM.messenger.message[params.message.id]
					)
					{
						return ;
					}

					this.readMessage(params.message.recipientId, false, false);

					data.USERS_MESSAGE[params.message.recipientId] = [params.message.id];

					this.updateStateVar(data);

					BX.MessengerCommon.recentListAdd({
						'userId': params.message.recipientId,
						'id': params.message.id,
						'date': params.message.date,
						'recipientId': params.message.recipientId,
						'senderId': params.message.system == 'Y'? 0: params.message.senderId,
						'text': messageText,
						'userIsChat': command == 'messageChat',
						'params': params.message.params
					}, true);
				}
				else
				{
					data.UNREAD_MESSAGE = {};
					if (params.notify === true || params.notify.indexOf(parseInt(this.BXIM.userId)) > -1)
					{
						data.UNREAD_MESSAGE[command == 'messageChat'? params.message.recipientId: params.message.senderId] = [params.message.id];
					}
					data.USERS_MESSAGE[command == 'messageChat'?params.message.recipientId: params.message.senderId] = [params.message.id];

					if (command == 'message')
						this.endWriting(params.message.senderId, 0, false);
					else
						this.endWriting(params.message.senderId, params.message.recipientId, false);

					if (command == 'messageChat' && !BX.MessengerCommon.userInChat(params.message.chatId))
					{
						this.updateStateVar(data);

						return ;
					}
					else
					{
						this.updateStateVar(data);

						var addToRecent = params.notify !== true && params.notify.indexOf(parseInt(this.BXIM.userId)) == -1? this.inRecentList(command == 'messageChat'? params.message.recipientId: params.message.senderId): true;
						if (addToRecent)
						{
							this.recentListAdd({
								'userId': command == 'messageChat'? params.message.recipientId: params.message.senderId,
								'id': params.message.id,
								'date': params.message.date,
								'recipientId': params.message.recipientId,
								'senderId': params.message.senderId,
								'text': messageText,
								'userIsChat': command == 'messageChat',
								'params': params.message.params
							}, true);
						}
					}
				}
				BX.localStorage.set('mfm', this.BXIM.messenger.flashMessage, 80);
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
				this.recentListElementUpdate(dialogId, params.id, params.text);
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

					this.BXIM.messenger.message[params.id].text = params.text;

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
									var newText = this.prepareText(this.BXIM.messenger.message[params.id].text, false, true, true);
									for (var i = 0; i < params.params.DATE_TEXT.length; i++)
									{
										newText = newText.split(params.params.DATE_TEXT[i]).join('<span class="bx-messenger-ajax bx-messenger-ajax-black" data-entity="date" data-messageId="'+params.id+'" data-ts="'+params.params.DATE_TS[i]+'">'+params.params.DATE_TEXT[i]+'</span>');
									}
									messageBox.innerHTML = newText;
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
									var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
									var keyboardNode = BX.MessengerCommon.drawKeyboard(this.BXIM.messenger.currentTab, params.id, params.params.KEYBOARD);
									if (messageKeyboardBox)
									{
										messageKeyboardBox.innerHTML = keyboardNode? keyboardNode.innerHTML: "";
									}
								}
							}
							else if (typeof(params.params) != 'undefined' && params.params == '')
							{
								if (BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
								{
									BX.remove(messageBox.nextElementSibling);
								}
							}
						}

						if (!textAlreadyUpdated)
						{
							messageBox.innerHTML = BX.MessengerCommon.prepareText(this.BXIM.messenger.message[params.id].text, false, true, true);
						}

						BX.addClass(messageBox, 'bx-messenger-message-edited-anim');
						if (messageBox.previousSibling && BX.hasClass(messageBox.previousSibling, 'bx-messenger-file-box'))
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
							var newText = this.prepareText(this.BXIM.messenger.message[params.id].text, false, true, true);
							for (var i = 0; i < params.params.DATE_TEXT.length; i++)
							{
								newText = newText.split(params.params.DATE_TEXT[i]).join('<span class="bx-messenger-ajax bx-messenger-ajax-black" data-entity="date" data-messageId="'+params.id+'" data-ts="'+params.params.DATE_TS[i]+'">'+params.params.DATE_TEXT[i]+'</span>');
							}
							messageBox.innerHTML = newText;
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
								filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(params.text != ''? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
								if (messageBox.previousElementSibling)
								{
									messageBox.parentNode.insertBefore(filesNode, messageBox.previousElementSibling);
								}
								else
								{
									messageBox.parentNode.insertBefore(filesNode, messageBox);
								}
							}
							if (messageBox.innerHTML != '' && BX.hasClass(messageBox.previousElementSibling, 'bx-messenger-file-box'))
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
							var messageKeyboardBox = BX('im-message-keyboard-'+params.id);
							var keyboardNode = BX.MessengerCommon.drawKeyboard(this.BXIM.messenger.currentTab, params.id, params.params.KEYBOARD);
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
					if (params.params && typeof(params.params.MENU) != 'undefined')
					{
						var messageParentBox = BX.findParent(messageBox, {className: 'bx-messenger-content-item'});
						var messageMenu = BX.findChildByClassName(messageParentBox, 'bx-messenger-content-item-menu');
						if (!params.params.MENU || params.params.MENU == 'N' || params.params.MENU.length <= 0)
						{
							BX.removeClass(messageMenu, 'bx-messenger-content-item-menu-with-apps');
						}
						else
						{
							BX.addClass(messageMenu, 'bx-messenger-content-item-menu-with-apps');
						}
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
								BX.addClass(element.firstChild, 'bx-messenger-content-item-plus-like');
								setTimeout(function(){
									BX.removeClass(element.firstChild, 'bx-messenger-content-item-plus-like');
								}, 500);
							}
							elementLikeDigit.innerHTML = likeCount;
						}
					}
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

				var openNewChat = this.BXIM.messenger.currentTab == params.dialogId;

				this.recentListHide(params.dialogId, false);

				if (!this.isMobile() && openNewChat)
				{
					BX.MessengerCommon.dialogCloseCurrent();
				}
			}
			else if (command == 'chatPin')
			{
				if (this.MobileActionNotEqual('RECENT'))
					return false;

				this.recentListElementPin(params.dialogId, params.active);
			}
			else if (command == 'chatUserAdd')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				for (var i in params.users)
				{
					params.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
					params.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
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
			else if (command == 'chatUserLeave')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (params.userId == this.BXIM.userId)
				{
					this.readMessage('chat'+params.chatId, true, false, true);
					this.leaveFromChat(params.chatId, false);
					if (params.message.length > 0)
						this.BXIM.openConfirm({title: BX.util.htmlspecialchars(params.chatTitle), message: params.message});
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
			else if (command == 'deleteNotifies')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				if (this.BXIM.notify.skipMassDelete)
				{
					return true;
				}
				for (var i in params.id)
				{
					if (params.id[i] > 0)
					{
						delete this.BXIM.notify.notify[i];
						delete this.BXIM.notify.flashNotify[i];
						delete this.BXIM.notify.unreadNotify[i];
					}
				}
				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.notifyOpen)
					this.BXIM.notify.openNotify(true);
			}
			else if (command == 'notify')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				params.date = new Date(params.date);
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
						this.BXIM.notify.viewNotify(params.id);
					}
				}

				if (params.silent == 'N')
					this.BXIM.notify.changeUnreadNotify(data.UNREAD_NOTIFY);

				BX.localStorage.set('mfn', this.BXIM.notify.flashNotify, 80);
				this.BXIM.lastRecordId = parseInt(params.id) > this.BXIM.lastRecordId? parseInt(params.id): this.BXIM.lastRecordId;
			}
			else if (command == 'readNotifyList')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				this.BXIM.notify.initNotifyCount = params.counter;

				params.list.forEach(function(id){
					delete this.BXIM.notify.unreadNotify[id];
				}.bind(this));

				this.BXIM.notify.viewNotifyMarkupUpdate();
				this.BXIM.notify.updateNotifyCount(false);
			}
			else if (command == 'massReadNotify')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				if (!BX.type.isArray(params.idList))
					return false;

				var notifyIdList = params.idList;
				this.BXIM.notify.initNotifyCount = 0;
				for (var i in this.BXIM.notify.unreadNotify)
				{
					var notify = this.BXIM.notify.notify[this.BXIM.notify.unreadNotify[i]];
					if (
						notify
						&& notify.type != 1
						&& notifyIdList.indexOf(notify.id) >= 0
					)
					{
						delete this.BXIM.notify.unreadNotify[i];
					}
				}
				this.BXIM.notify.updateNotifyCount(false);
			}
			else if (command == 'confirmNotify')  // TODO mobile
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
			else if (command == 'readNotifyOne')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				if (this.BXIM.notify.unreadNotify[params.id])
				{
					this.BXIM.notify.viewNotify(params.id, true, false);
				}
			}
			else if (command == 'unreadNotifyList')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				params.list.forEach(function(id){
					this.BXIM.notify.viewNotify(id, false, false);
				}.bind(this));
			}
			else if (command == 'deleteCommand')
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
			else if (command == 'deleteAppIcon')
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

					var element = BX.findChildByClassName(this.BXIM.messenger.popupMessengerTextareaIconBox, 'bx-messenger-textarea-icon-marketplace-'+params.iconId, true);
					if (element)
					{
						BX.remove(element);
					}

					break;
				}
			}
			else if (command == 'updateAppIcon')
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
			else if (command == 'chatUpdateParam')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.chat[params.chatId])
				{
					if (params.name == 'name')
					{
						params.value = BX.util.htmlspecialchars(params.value)
					}
					this.BXIM.messenger.chat[params.chatId][params.name] = params.value;
					if (this.BXIM.messenger.currentTab.toString().substr(4) == params.chatId)
					{
						this.BXIM.messenger.redrawChatHeader();
						if (this.isMobile())
						{
							this.BXIM.messenger.dialogStatusRedraw();
						}
					}
					if (
						this.BXIM.messenger.chat[params.chatId].type == 'livechat' &&
						params.fieldName == 'entity_data_1'
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
					if (this.MobileActionEqual('RECENT') && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
					{
						this.recentListRedraw();
					}
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
				var contactListRedraw = false;
				var dialogStatusRedraw = false;

				for (var i in params.users)
				{
					if (typeof(this.BXIM.messenger.users[i]) == 'undefined')
					{
						continue;
					}
					if (this.BXIM.messenger.recentListIndex.indexOf(i.toString()) >= 0)
					{
						contactListRedraw = true;
					}
					if (this.BXIM.messenger.currentTab.toString() == i.toString())
					{
						dialogStatusRedraw = true;
					}

					this.BXIM.messenger.users[i].status = params.users[i].status;
					this.BXIM.messenger.users[i].color = params.users[i].color;
					this.BXIM.messenger.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
					this.BXIM.messenger.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
					this.BXIM.messenger.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
				}

				if (contactListRedraw)
				{
					BX.MessengerCommon.userListRedraw();
				}
				if (dialogStatusRedraw)
				{
					this.BXIM.messenger.dialogStatusRedraw();
				}
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
				data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
				data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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

		if (typeof(data.MESSAGE) != "undefined")
		{
			for (var i in data.MESSAGE)
			{
				data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
				if (this.BXIM.messenger.message[i] && this.BXIM.messenger.message[i].dropDuplicate)
				{
					data.MESSAGE[i].dropDuplicate = true;
				}
				this.BXIM.messenger.message[i] = data.MESSAGE[i];
				this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
			}
		}

		this.changeUnreadMessage(data.UNREAD_MESSAGE, send);

		if (typeof(data.USERS_MESSAGE) != "undefined")
		{
			for (var i in data.USERS_MESSAGE)
			{
				data.USERS_MESSAGE[i].sort(BX.delegate(function(i, ii) {i = parseInt(i); ii = parseInt(ii); if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = this.BXIM.messenger.message[i].date.getTime(); var i2 = this.BXIM.messenger.message[ii].date.getTime(); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
				for (var j = 0; j < data.USERS_MESSAGE[i].length; j++)
				{
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
						this.BXIM.messenger.showMessage[i].push(data.USERS_MESSAGE[i][j]);
						if (this.BXIM.messenger.history[i])
							this.BXIM.messenger.history[i] = BX.util.array_merge(this.BXIM.messenger.history[i], data.USERS_MESSAGE[i]);
						else
							this.BXIM.messenger.history[i] = data.USERS_MESSAGE[i];

						if (writeMessage && this.BXIM.messenger.currentTab == i && this.MobileActionEqual('DIALOG'))
							this.drawMessage(i, this.BXIM.messenger.message[data.USERS_MESSAGE[i][j]]);
					}
				}
			}
		}
	};

	MessengerCommon.prototype.changeUnreadMessage = function(unreadMessage, send)
	{
		send = send != false;

		var playSound = false;
		var contactListRedraw = false;
		var needRedrawDialogStatus = true;

		var userStatus = this.isMobile()? 'online': this.BXIM.settings.status;

		for (var i in unreadMessage)
		{
			if (i.toString().substr(0, 4) == 'chat')
			{
				if (!BX.MessengerCommon.userInChat(i.toString().substr(4)))
				{
					continue;
				}
			}

			var skipPopup = false;
			if (this.BXIM.xmppStatus && i.toString().substr(0,4) != 'chat')
			{
				if (!(this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i && this.BXIM.isFocus()))
				{
					contactListRedraw = true;
					if (this.BXIM.messenger.unreadMessage[i])
						this.BXIM.messenger.unreadMessage[i] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[i], unreadMessage[i]));
					else
						this.BXIM.messenger.unreadMessage[i] = unreadMessage[i];
				}
				skipPopup = true;
			}

			if (!skipPopup)
			{
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i && this.BXIM.isFocus())
				{
					if (typeof (this.BXIM.messenger.flashMessage[i]) == 'undefined')
						this.BXIM.messenger.flashMessage[i] = {};

					for (var k = 0; k < unreadMessage[i].length; k++)
					{
						if (this.BXIM.isFocus())
							this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;

						if (this.BXIM.messenger.message[unreadMessage[i][k]] && this.BXIM.messenger.message[unreadMessage[i][k]].senderId == this.BXIM.messenger.currentTab)
							playSound = true;
					}
					this.readMessage(i, true, true, true);
				}
				else if (this.isMobile() && this.BXIM.messenger.currentTab == i)
				{
					var dialogId = this.BXIM.messenger.currentTab;
					this.BXIM.isFocusMobile(BX.delegate(function(visible){
						if (visible)
						{
							BX.MessengerCommon.readMessage(dialogId, true, true, true);
						}
					},this));
					if (this.BXIM.messenger.unreadMessage[dialogId])
						this.BXIM.messenger.unreadMessage[dialogId] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[dialogId], unreadMessage[dialogId]));
					else
						this.BXIM.messenger.unreadMessage[dialogId] = unreadMessage[dialogId];
				}
				else
				{
					contactListRedraw = true;

					if (typeof (this.BXIM.messenger.flashMessage[i]) == 'undefined')
					{
						this.BXIM.messenger.flashMessage[i] = {};
						var isLines = i.toString().substr(0,4) == 'chat' && this.BXIM.messenger.chat[i.toString().substr(4)] && this.BXIM.messenger.chat[i.toString().substr(4)].type == 'lines';
						for (var k = 0; k < unreadMessage[i].length; k++)
						{
							if (isLines && this.BXIM.messenger.unreadMessage[i] && this.BXIM.messenger.unreadMessage[i].length > 0)
							{
								var senderId = this.BXIM.messenger.message[unreadMessage[i][k]].senderId;
								if (senderId == 0 || this.BXIM.messenger.users[senderId].extranet)
								{
									this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
									continue;
								}
							}

							var resultOfNameSearch = this.BXIM.messenger.message[unreadMessage[i][k]].text.match(new RegExp("("+this.BXIM.messenger.users[this.BXIM.userId].name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'));
							if (userStatus != 'dnd' || resultOfNameSearch)
							{
								this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = send;
							}
						}
					}
					else
					{
						var isLines = i.toString().substr(0,4) == 'chat' && this.BXIM.messenger.chat[i.toString().substr(4)] && this.BXIM.messenger.chat[i.toString().substr(4)].type == 'lines';
						for (var k = 0; k < unreadMessage[i].length; k++)
						{
							var resultOfNameSearch = this.BXIM.messenger.message[unreadMessage[i][k]].text.match(new RegExp("("+this.BXIM.messenger.users[this.BXIM.userId].name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'));
							if (userStatus != 'dnd' || resultOfNameSearch)
							{
								if (!send && !this.BXIM.isFocus())
								{
									this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
								}
								else
								{
									if (isLines && this.BXIM.messenger.unreadMessage[i] && this.BXIM.messenger.unreadMessage[i].length > 0)
									{
										var senderId = this.BXIM.messenger.message[unreadMessage[i][k]].senderId;
										if (senderId == 0 || this.BXIM.messenger.users[senderId].extranet)
										{
											this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
											continue;
										}
									}
									if (typeof (this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]]) == 'undefined')
									{
										this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = true;
									}
								}
							}
						}
					}
					if (this.BXIM.messenger.unreadMessage[i])
						this.BXIM.messenger.unreadMessage[i] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[i], unreadMessage[i]));
					else
						this.BXIM.messenger.unreadMessage[i] = unreadMessage[i];

				}
			}

			if (this.MobileActionEqual('DIALOG') && this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i)
			{
				needRedrawDialogStatus = true;
			}
		}
		if (needRedrawDialogStatus)
		{
			this.BXIM.messenger.dialogStatusRedraw(this.isMobile()? {type: 1, slidingPanelRedrawDisable: true, 'userRedraw': false}: {'userRedraw': false});
		}

		if (this.MobileActionEqual('RECENT') && this.BXIM.messenger.popupMessenger != null && !this.BXIM.messenger.recentList && contactListRedraw)
			BX.MessengerCommon.userListRedraw();

		if (this.isMobile() && this.MobileActionEqual('RECENT') && app.enableInVersion(13))
		{
			clearTimeout(this.newMessageTimeout);
			this.newMessageTimeout = setTimeout(BX.proxy(function(){
				this.BXIM.messenger.newMessage();
			}, this), 1000);
		}
		else if (!this.isMobile())
		{
			this.BXIM.messenger.newMessage(send);
			this.BXIM.messenger.updateMessageCount(send);

			if (send && playSound && userStatus != 'dnd')
			{
				this.BXIM.playSound("newMessage2");
			}
		}
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
		showMessage = this.BXIM.messenger.showMessage[dialogId];
		showMessage.sort(function(i, ii) {if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}});

		var lastId = 0;
		this.BXIM.messenger.unreadMessage[dialogId] = [];
		for (var i = 0; i < showMessage.length; i++)
		{
			if (showMessage[i] >= messageId)
			{
				if (!this.BXIM.messenger.unreadMessage[dialogId])
					this.BXIM.messenger.unreadMessage[dialogId] = [];

				this.BXIM.messenger.unreadMessage[dialogId].push(showMessage[i]);
			}
			else
			{
				lastId = showMessage[i];
			}
		}

		this.skipReadMessage = true;

		this.drawTab();
		this.userListRedraw();

		setTimeout(BX.delegate(function(){
			this.skipReadMessage = false;
		},this), 1000);

		var _ajax = BX.ajax({
			url: this.BXIM.pathToAjax+'?UNREAD_MESSAGE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			skipAuthCheck: true,
			data: {'IM_UNREAD_MESSAGE' : 'Y', 'USER_ID' : dialogId, 'LAST_ID': lastId, 'TAB' : this.BXIM.messenger.currentTab, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		})

	}

	MessengerCommon.prototype.readMessage = function(userId, send, sendAjax, skipCheck)
	{
		if (!userId || this.skipReadMessage)
			return false;

		skipCheck = skipCheck == true || this.isMobile();
		if (!skipCheck && (!this.BXIM.messenger.unreadMessage[userId] || this.BXIM.messenger.unreadMessage[userId].length <= 0))
			return false;

		if (userId.toString().substring(0, 4) == 'chat')
		{
			var chatId = userId.toString().substring(4);
			if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type == 'lines' && this.BXIM.messenger.chat[chatId].owner == 0)
			{
				return false;
			}
		}

		send = send != false;
		sendAjax = sendAjax !== false;

		var userWithMessage = {};
		for (var i in this.BXIM.messenger.unreadMessage)
		{
			if (userId == i)
				continue;

			userWithMessage[i] = true;
		}

		if (this.BXIM.messenger.recentListExternal)
		{
			var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-status-new-message");
			if (elements != null)
			{
				for (var i = 0; i < elements.length; i++)
				{
					var recentUserId = elements[i].getAttribute('data-userId');
					if (!userWithMessage[recentUserId])
					{
						elements[i].firstChild.innerHTML = '';
						BX.removeClass(elements[i], 'bx-messenger-cl-status-new-message');
					}
				}
			}
		}
		if (this.BXIM.messenger.popupMessenger != null)
		{
			var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-status-new-message");
			if (elements != null)
			{
				for (var i = 0; i < elements.length; i++)
				{
					var recentUserId = elements[i].getAttribute('data-userId');
					if (!userWithMessage[recentUserId])
					{
						elements[i].firstChild.innerHTML = '';
						BX.removeClass(elements[i], 'bx-messenger-cl-status-new-message');
					}
				}
			}

			elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-new", false);
			if (elements != null)
				for (var i = 0; i < elements.length; i++)
					if (elements[i].getAttribute('data-notifyType') != 1)
						BX.removeClass(elements[i], 'bx-messenger-content-item-new');
		}
		var lastId = 0;
		if (Math && this.BXIM.messenger.unreadMessage[userId])
			lastId = Math.max.apply(Math, this.BXIM.messenger.unreadMessage[userId]);

		if (this.BXIM.messenger.unreadMessage[userId])
		{
			var unreadedMessageUserBackup = BX.clone(this.BXIM.messenger.unreadMessage[userId]);
			delete this.BXIM.messenger.unreadMessage[userId];
		}

		if (this.BXIM.messenger.flashMessage[userId])
			delete this.BXIM.messenger.flashMessage[userId];

		BX.localStorage.set('mfm', this.BXIM.messenger.flashMessage, 80);

		if (!this.isMobile())
		{
			this.BXIM.messenger.updateMessageCount(send);
			this.BXIM.updateCounter();
		}

		if (sendAjax)
		{
			//clearTimeout(this.BXIM.messenger.readMessageTimeout[userId+'_'+this.BXIM.messenger.currentTab]);
			//this.BXIM.messenger.readMessageTimeout[userId+'_'+this.BXIM.messenger.currentTab] = setTimeout(BX.delegate(function(){
				var sendData = {'IM_READ_MESSAGE' : 'Y', 'USER_ID' : userId, 'TAB' : this.BXIM.messenger.currentTab, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
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
								BX.onCustomEvent(window, 'onImMessageRead', [userId]);
								this.BXIM.messenger.setUpdateStateStep();
							}
							else
							{
								this.BXIM.messenger.unreadMessage[userId] = unreadedMessageUserBackup;
								if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
								{
									this.BXIM.messenger.sendAjaxTry++;
									setTimeout(BX.delegate(function(){
										this.readMessage(userId, false, true);
									}, this), 2000);
									BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
								}
								else if (data.ERROR == 'AUTHORIZE_ERROR')
								{
									this.BXIM.messenger.sendAjaxTry++;
									if (this.isDesktop() || this.isMobile())
									{
										setTimeout(BX.delegate(function(){
											this.readMessage(userId, false, true);
										}, this), 10000);
									}
									BX.onCustomEvent(window, 'onImError', [data.ERROR]);
								}
							}
						}
						else
						{
							this.BXIM.messenger.unreadMessage[userId] = unreadedMessageUserBackup;
						}
					}, this),
					onfailure: BX.delegate(function()
					{
						this.BXIM.messenger.unreadMessage[userId] = unreadedMessageUserBackup;

						this.BXIM.messenger.sendAjaxTry = 0;
						try {
							if (typeof(_ajax) == 'object' && _ajax.status == 0)
								BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
						}
						catch(e) {}
					}, this)
				});
			//}, this), 200);
		}
		if (send)
		{
			BX.localStorage.set('mrm', userId, 5);
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
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupHistoryBodyWrap, "bx-messenger-history-item-text");
			}
			else
			{
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-text-wrap");
			}

			if (!this.isMobile() && elements.length < 20 && !loadFromButton)
			{
				return false;
			}

			if (elements.length > 0)
				this.BXIM.messenger.historyOpenPage[userId] = Math.floor(elements.length/20)+1;
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
						this.BXIM.messenger.message[i] = data.MESSAGE[i];

						countMessages++;
					}
					if (countMessages < 20)
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
							BX.scrollToNode(lastChildBeforeChangeDom.parentNode.parentNode.parentNode.parentNode.parentNode);
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
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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
					if (data.ERROR == 'ACCESS_DENIED')
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
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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

		this.BXIM.messenger.historyWindowBlock = true;

		delete this.BXIM.messenger.redrawTab[userId];
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

			if (this.BXIM.messenger.popupMessengerDialog && this.BXIM.messenger.currentTab == data.USER_ID)
			{
				BX.removeClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message");
			}
			if (!this.BXIM.checkRevision(this.isMobile()? data.MOBILE_REVISION: data.REVISION))
				return false;

			if (!data)
			{
				onfailure();
				return false;
			}

			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}

			if (data.ERROR == '')
			{
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
				}
				else
				{
					this.BXIM.messenger.userChat[userId] = data.CHAT_ID;

					BX.MessengerCommon.getUserParam(userId, true);
					this.BXIM.messenger.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');
				}

				for (var i in data.USERS)
				{
					data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
					data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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
					this.BXIM.messenger.message[i] = data.MESSAGE[i];
					this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
				}

				if (messageCnt <= 0)
				{
					delete this.BXIM.messenger.redrawTab[data.USER_ID];
				}

				for (var i in data.USERS_MESSAGE)
				{
					if (this.BXIM.messenger.showMessage[i])
						this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
					else
						this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
				}
				if (userIsChat && this.BXIM.messenger.chat[data.USER_ID.toString().substr(4)].fake)
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

					if (this.MobileActionEqual('RECENT'))
					{
						var countDupl = 0;
						for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
						{
							if (this.BXIM.messenger.recent[i].userId == data.NETWORK_ID)
							{
								countDupl++;
								this.BXIM.messenger.recent[i].userId = data.USER_ID;
								this.BXIM.messenger.recent[i].recipientId = data.USER_ID;
								this.BXIM.messenger.recent[i].senderId = data.USER_ID;
							}
							else if (this.BXIM.messenger.recent[i].userId == data.USER_ID)
							{
								countDupl++;
							}
						}
						if (countDupl > 1)
						{
							for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
							{
								if (this.BXIM.messenger.recent[i].userId == data.USER_ID)
								{
									this.recentListHide(data.USER_ID, false);
									break;
								}
							}
						}
						BX.MessengerCommon.userListRedraw();
					}
					else if (this.isMobile() && this.MobileActionEqual('DIALOG'))
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
					this.readMessage(data.USER_ID, true, false);
				}

				if (this.isMobile())
				{
					setTimeout(BX.delegate(function(){this.BXIM.messenger.autoScroll()}, this), 100);
				}

				callback(userId, true, data);
			}
			else
			{
				this.BXIM.messenger.redrawTab[userId] = true;
				if (data.ERROR == 'ACCESS_DENIED')
				{
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
				'READ' : readMessage? 'Y': 'N',
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

	MessengerCommon.prototype.openDialog = function(userId, extraClose, callToggle)
	{
		var user = BX.MessengerCommon.getUserParam(userId);
		if (user.id <= 0)
			return false;

		this.BXIM.messenger.currentTab = userId? userId: 0;
		if (userId.toString().substr(0,4) == 'chat')
		{
			this.BXIM.messenger.openChatFlag = true;
			if (this.BXIM.messenger.chat[userId.toString().substr(4)] && this.BXIM.messenger.chat[userId.toString().substr(4)].type == 'call')
				this.BXIM.messenger.openCallFlag = true;
			else if (this.BXIM.messenger.chat[userId.toString().substr(4)] && this.BXIM.messenger.chat[userId.toString().substr(4)].type == 'lines')
				this.BXIM.messenger.openLinesFlag = true;
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
			this.BXIM.messenger.popupMessengerPanel.className  = this.BXIM.messenger.openChatFlag? 'bx-messenger-panel bx-messenger-hide': 'bx-messenger-panel';
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
		callToggle = callToggle != false;

		var arMessage = [];
		if (typeof(this.BXIM.messenger.showMessage[userId]) != 'undefined' && this.BXIM.messenger.showMessage[userId].length > 0)
		{
			if (
				this.BXIM.messenger.showMessage[userId] && this.BXIM.messenger.unreadMessage[userId] &&
				this.BXIM.messenger.showMessage[userId].length != 0
				&& this.BXIM.messenger.showMessage[userId].length == this.BXIM.messenger.unreadMessage[userId].length
			)
			{
				this.drawTab(userId, true);

				BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
				var loading = BX.create("div", { props : { className : "bx-notifier-content-link-history"}, children : [
					BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
					BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_LOAD_MESSAGE')})
				]});
				this.BXIM.messenger.redrawTab[userId] = true;

				this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(loading, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);

				if (this.isMobile())
				{
					setTimeout(BX.delegate(function(){this.BXIM.messenger.autoScroll()}, this), 100);
				}
			}
			else if (!user.fake && this.BXIM.messenger.showMessage[userId].length >= 15)
			{
				this.BXIM.messenger.redrawTab[userId] = false;
			}
			else
			{
				this.drawTab(userId, true);
				this.BXIM.messenger.redrawTab[userId] = true;
			}
		}
		else if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_ERROR")})
			]})];
			this.BXIM.messenger.redrawTab[userId] = true;
		}
		else if (typeof(this.BXIM.messenger.showMessage[userId]) == 'undefined')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_LOAD_MESSAGE')})
			]})];
			this.BXIM.messenger.redrawTab[userId] = true;
		}
		else if (this.BXIM.messenger.redrawTab[userId] && this.BXIM.messenger.showMessage[userId].length == 0)
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_MESSAGE")})
			]})];
			this.BXIM.messenger.showMessage[userId] = [];
		}
		else
		{
			var messageEmpty = "";
			if (this.isBot(userId) && this.BXIM.messenger.users[userId])
			{
				messageEmpty = BX.message("IM_M_NO_MESSAGE_BOT").replace('#BOT_NAME#', this.BXIM.messenger.users[userId].name);
			}
			else
			{
				messageEmpty = BX.message(this.BXIM.settings.loadLastMessage? "IM_M_NO_MESSAGE_2": "IM_M_NO_MESSAGE");
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
			BXMobileApp.UI.Page.TextPanel.setText(this.BXIM.messenger.textareaHistory[userId]? this.BXIM.messenger.textareaHistory[userId]: "");
		}
		else
		{
			this.BXIM.messenger.popupMessengerTextarea.value = this.BXIM.messenger.textareaHistory[userId]? this.BXIM.messenger.textareaHistory[userId]: "";
		}

		if (this.BXIM.messenger.redrawTab[userId])
		{
			if (this.BXIM.settings.loadLastMessage)
			{
				this.loadLastMessage(userId);
			}
			else
			{
				if (this.BXIM.messenger.openChatFlag)
					BX.MessengerCommon.loadChatData(userId.toString().substr(4));
				else
					BX.MessengerCommon.loadUserData(userId);

				delete this.BXIM.messenger.redrawTab[userId];
				this.drawTab(userId, true);
			}
		}
		else
		{
			this.drawTab(userId, true);
		}

		if (!this.BXIM.messenger.redrawTab[userId])
		{
			if (this.isMobile())
			{
				this.BXIM.isFocusMobile(BX.delegate(function(visible){
					if (visible)
					{
						BX.MessengerCommon.readMessage(userId);
					}
				},this));
			}
			else if (this.BXIM.isFocus())
			{
				this.readMessage(userId);
			}
		}

		if (!this.isMobile())
			this.BXIM.messenger.resizeMainWindow();

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

		if (!this.isMobile() && callToggle)
			this.BXIM.webrtc.callOverlayToggleSize(true);

		BX.onCustomEvent("onImDialogOpen", [{id: userId}]);
		if (this.isMobile())
		{
			BXMobileApp.onCustomEvent('onImDialogOpen', {'id': userId}, true);
		}
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
				else if (this.BXIM.messenger.chat[chatId].type == 'lines')
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
			else if (this.BXIM.settings.linesTabEnable)
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
				messageEmpty = BX.message(this.BXIM.settings.loadLastMessage? "IM_M_NO_MESSAGE_2": "IM_M_NO_MESSAGE");
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
			BX.MessengerCommon.drawMessage(userId, this.BXIM.messenger.message[this.BXIM.messenger.showMessage[userId][i]], false);

		if (messageCount > 0 && messageCount < 20)
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

		scroll = scroll != false;
		if (scroll)
		{
			if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
				this.BXIM.messenger.popupMessengerBodyAnimation.stop();

			if (this.BXIM.messenger.unreadMessage[userId] && this.BXIM.messenger.unreadMessage[userId].length > 0)
			{
				var textElement = BX('im-message-'+this.BXIM.messenger.unreadMessage[userId][0]);
				if (textElement && textElement.parentNode.parentNode.parentNode.parentNode.parentNode)
				{
					BX.scrollToNode(textElement.parentNode.parentNode.parentNode.parentNode.parentNode);
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

		delete this.BXIM.messenger.redrawTab[userId];
	};



	/* Section: Send Message */
	MessengerCommon.prototype.sendMessageAjax = function(messageTmpIndex, recipientId, messageText, sendMessageToChat)
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

		var olSilentMode = 'N';
		if (sendMessageToChat && this.BXIM.messenger.linesSilentMode && this.BXIM.messenger.linesSilentMode[recipientId.toString().substr(4)])
		{
			olSilentMode = 'Y';
		}

		this.recentListAdd({
			'id': 'temp'+messageTmpIndex,
			'date': new Date(),
			'skipDateCheck': true,
			'recipientId': recipientId,
			'senderId': this.BXIM.userId,
			'text': messageText,
			'userId': recipientId,
			'userIsChat': sendMessageToChat,
			'params': {CLASS: olSilentMode == 'Y'? 'bx-messenger-content-item-system': ''}
		}, true);

		BX.onCustomEvent('onImBeforeMessageSend', [{recipientId: recipientId, messageText: messageText}]);

		var _ajax = BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_SEND&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 120,
			data: {'IM_SEND_MESSAGE' : 'Y', 'CHAT': sendMessageToChat? 'Y': 'N', 'ID' : 'temp'+messageTmpIndex, 'RECIPIENT_ID' : recipientId, 'MESSAGE' : messageText, 'OL_SILENT': olSilentMode, 'TAB' : this.BXIM.messenger.currentTab, 'USER_TZ_OFFSET': BX.message('USER_TZ_OFFSET'), 'IM_AJAX_CALL' : 'Y', 'FOCUS' : !this.isMobile() || typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground()? 'N': 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				this.BXIM.messenger.sendMessageFlag--;

				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}

				if (data && data.ERROR == '')
				{
					this.BXIM.messenger.sendAjaxTry = 0;
					this.BXIM.messenger.message[data.TMP_ID].text = data.SEND_MESSAGE;
					this.BXIM.messenger.message[data.TMP_ID].id = data.ID;
					this.BXIM.messenger.message[data.TMP_ID].date = new Date(data.SEND_DATE);

					if (data.SEND_MESSAGE_PARAMS)
					{
						this.BXIM.messenger.message[data.TMP_ID].params = data.SEND_MESSAGE_PARAMS;
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

					this.BXIM.messenger.message[data.ID] = this.BXIM.messenger.message[data.TMP_ID];

					if (this.BXIM.messenger.popupMessengerLastMessage == data.TMP_ID)
						this.BXIM.messenger.popupMessengerLastMessage = data.ID;

					delete this.BXIM.messenger.message[data.TMP_ID];
					var message = this.BXIM.messenger.message[data.ID];

					var idx = BX.util.array_search(''+data.TMP_ID+'', this.BXIM.messenger.showMessage[data.RECIPIENT_ID]);
					if (this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx])
						this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx] = ''+data.ID+'';

					for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
					{
						if (this.BXIM.messenger.recent[i].id == data.TMP_ID)
						{
							this.BXIM.messenger.recent[i].id = ''+data.ID+'';
							break;
						}
					}

					if (data.RECIPIENT_ID == this.BXIM.messenger.currentTab)
					{
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.TMP_ID+''}}, true);
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

						var textElement = BX('im-message-'+data.TMP_ID);
						if (textElement)
						{
							textElement.id = 'im-message-'+data.ID;
							textElement.innerHTML =  BX.MessengerCommon.prepareText(data.SEND_MESSAGE, false, true, true);
						}

						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
							lastMessageElementDate.innerHTML = BX.MessengerCommon.formatDate(message.date, BX.MessengerCommon.getDateFormatType('MESSAGE'));

						BX.MessengerCommon.clearProgessMessage(data.ID);
					}

					if (this.BXIM.messenger.history[data.RECIPIENT_ID])
						this.BXIM.messenger.history[data.RECIPIENT_ID].push(message.id);
					else
						this.BXIM.messenger.history[data.RECIPIENT_ID] = [message.id];

					this.BXIM.messenger.updateStateVeryFastCount = 2;
					this.BXIM.messenger.updateStateFastCount = 5;
					this.BXIM.messenger.setUpdateStateStep();

					if (data.SEND_MESSAGE_PARAMS)
					{
						if (data.SEND_MESSAGE_PARAMS.URL_ONLY == 'Y' && this.BXIM.settings.enableRichLink)
						{
							BX.addClass(element.firstElementChild, 'bx-messenger-content-item-content-rich-link');
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
										"im_revision": this.BXIM.revision
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
									"im_revision": this.BXIM.revision
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
										"im_revision": this.BXIM.revision
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
									"im_revision": this.BXIM.revision
								}]);
							}
						}
					}

					if (BX.PULL)
					{
						BX.PULL.setUpdateStateStepCount(2,5);
					}
					BX.MessengerCommon.updateStateVar(data, true, true);
					BX.localStorage.set('msm', {'id': data.ID, 'recipientId': data.RECIPIENT_ID, 'date': data.SEND_DATE, 'text' : data.SEND_MESSAGE, 'senderId' : this.BXIM.userId, 'MESSAGE': data.MESSAGE, 'USERS_MESSAGE': data.USERS_MESSAGE, 'USERS': data.USERS, 'USER_IN_GROUP': data.USER_IN_GROUP}, 5);


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

					if (this.MobileActionEqual('RECENT') && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
						this.recentListRedraw();
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
						if (lastMessageElementDate)
						{
							if (data.ERROR == 'SESSION_ERROR' || data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'UNKNOWN_ERROR' || data.ERROR == 'IM_MODULE_NOT_INSTALLED')
								lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');
							else
								lastMessageElementDate.innerHTML = data.ERROR;
						}
						BX.onCustomEvent(window, 'onImError', ['SEND_ERROR', data.ERROR, data.TMP_ID, data.SEND_DATE, data.SEND_MESSAGE, data.RECIPIENT_ID]);

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

			message.text = BX.MessengerCommon.prepareTextBack(message.text);

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
		clearTimeout(this.BXIM.messenger.sendMessageTmpTimeout[message.id]);
		this.BXIM.messenger.sendMessageTmpTimeout[message.id] = setTimeout(BX.delegate(function() {
			BX.MessengerCommon.sendMessageAjax(message.id.substr(4), message.recipientId, message.text, message.recipientId.toString().substr(0,4) == 'chat');
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
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type != 'open')
			return false;

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
		if (this.BXIM.messenger.bot[this.BXIM.messenger.currentTab] && this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'network')
		{
			return result;
		}

		if (
			this.BXIM.ppServerStatus && parseInt(id) != 0 && id.toString().substr(0,4) != 'temp' &&
			this.BXIM.messenger.message[id] &&
			(this.BXIM.messenger.message[id].date.getTime()/1000)+259200 > (new Date().getTime())/1000 &&
			(!this.BXIM.messenger.message[id].params || this.BXIM.messenger.message[id].params.IS_DELETED != 'Y') &&
			BX('im-message-'+id) && BX.util.in_array(id, this.BXIM.messenger.showMessage[this.BXIM.messenger.currentTab])
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

		if (text == BX.MessengerCommon.prepareTextBack(this.BXIM.messenger.message[id].text, true))
			return false;

		text = text.replace('    ', "\t");
		text = BX.util.trim(text);
		if (text.length <= 0)
		{
			BX.MessengerCommon.deleteMessageAjax(id);
			return false;
		}

		text = BX.MessengerCommon.prepareMention(this.BXIM.messenger.currentTab, text);

		BX.MessengerCommon.drawProgessMessage(id);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_EDIT&V='+this.BXIM.revision,
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

		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_DELETE&V='+this.BXIM.revision,
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
			url: this.BXIM.pathToAjax+'?MESSAGE_SHARE&TYPE='+type+'&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_SHARE_MESSAGE' : 'Y', ID: id, TYPE: type, DATE: date? date: 0, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				if (data.ERROR)
					return false;

				BX.MessengerCommon.clearProgessMessage(id);
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
			return null;

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
					buttonValue = '<span class="bx-messenger-keyboard-button-text" data-disabled="Y" style="'+textStyles+'">'+
						buttonConfig[i].TEXT+
					'</span>';
				}
				else
				{
					if (buttonConfig[i].LINK)
					{
						buttonValue = '<a href="'+buttonConfig[i].LINK+'" target="_blank" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							buttonConfig[i].TEXT+
						'</a>';
					}
					else if (buttonConfig[i].FUNCTION)
					{
						var userFunc = buttonConfig[i].FUNCTION.toString().replace('#MESSAGE_ID#', messageId).replace('#DIALOG_ID#', dialogId).replace('#USER_ID#', this.BXIM.userId);
						buttonValue = '<a href="javascript:void(1);" onclick="'+userFunc+'; BX.PreventDefault(event);" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							buttonConfig[i].TEXT+
						'</a>';
					}
					else if (buttonConfig[i].APP_ID)
					{
						buttonConfig[i].APP_PARAMS = buttonConfig[i].APP_PARAMS? buttonConfig[i].APP_PARAMS: '';
						buttonValue = '<a href="javascript:void(1);" onclick="BXIM.messenger.textareaIconDialogClick('+parseInt(buttonConfig[i].APP_ID)+', '+messageId+', \''+(BX.util.htmlspecialchars(buttonConfig[i].APP_PARAMS))+'\'); BX.PreventDefault(event);" class="bx-messenger-keyboard-button-text" style="'+textStyles+'">' +
							buttonConfig[i].TEXT+
						'</a>';
					}
					else
					{
						buttonValue = '<span class="bx-messenger-keyboard-button-text" data-dialogId="'+dialogId+'" data-messageId="'+messageId+'" data-blockAfterClick="'+buttonConfig[i].BLOCK+'" data-command="'+BX.util.htmlspecialchars(buttonConfig[i].COMMAND)+'" data-commandParams="'+BX.util.htmlspecialchars(buttonConfig[i].COMMAND_PARAMS)+'" data-botId="'+buttonConfig[i].BOT_ID+'" style="'+textStyles+'">'+
							buttonConfig[i].TEXT+
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

		return keyboardNode;
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
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'network', 'data-networkId': attach.USER[i].NETWORK_ID}, html: attach.USER[i].NAME});
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

							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'user', 'data-userId': attach.USER[i].BOT_ID}, html: attach.USER[i].NAME});
						}
						else if (attach.USER[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax "+(attach.USER[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-userId': attach.USER[i].USER_ID}, html: attach.USER[i].NAME});
							if (this.BXIM.messenger.users[attach.USER[i].USER_ID])
							{
								attach.USER[i].AVATAR = this.BXIM.messenger.users[attach.USER[i].USER_ID].avatar;
							}
						}
						else if (attach.USER[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.USER[i].CHAT_ID}, html: attach.USER[i].NAME});
						}
						else if (attach.USER[i].LINK)
						{
							linkTitle = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.USER[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-attach-user-name"}, html: attach.USER[i].NAME});
						}
						else
						{
							linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-user-name"}, html: attach.USER[i].NAME})
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
								attach.USER[i].AVATAR? BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.USER[i].AVATAR)}, props : { className: "bx-messenger-attach-user-avatar-img"}}): BX.create("span", { attrs: {style: "background-color: "+color}, props : { className: "bx-messenger-attach-user-avatar-img bx-messenger-attach-"+avatarType+"-avatar-default "}})
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
						var linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-link-name"}, html: attach.LINK[i].NAME? attach.LINK[i].NAME: attach.LINK[i].LINK});
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
								BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.LINK[i].LINK), 'target': '_blank'}, html: attach.LINK[i].NAME? attach.LINK[i].NAME: attach.LINK[i].LINK})
							]});
						}

						var linkDesc = null;
						if (attach.LINK[i].DESC)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-link-desc"}, html: attach.LINK[i].DESC});
						}

						var linkPreview = null;
						if (attach.LINK[i].HTML)
						{
							linkPreview = BX.create("div", { props : { className: "bx-messenger-attach-link-html"}, html: attach.LINK[i].HTML});
							var link = BX.create("span", {props : { className: "bx-messenger-attach-link"+(attach.LINK[i].PREVIEW? " bx-messenger-attach-link-with-preview": "")}, children: [linkTitle, linkDesc, linkPreview]})
						}
						else if (attach.LINK[i].PREVIEW)
						{
							linkPreview = BX.create("span", { props : { className: "bx-messenger-file-image-src"}, children: [
								BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.LINK[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this, true)"}, props : { className: "bx-messenger-file-image-text"}}),
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
						var linkSource = null;

						var linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-rich-link-name"}, html: attach.RICH_LINK[i].NAME? attach.RICH_LINK[i].NAME: attach.RICH_LINK[i].LINK});
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
									BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.RICH_LINK[i].LINK), 'target': '_blank'}, html: attach.RICH_LINK[i].NAME? attach.RICH_LINK[i].NAME: attach.RICH_LINK[i].LINK})
								]});
							}
							linkSource = BX.create("div", { props : { className: "bx-messenger-attach-rich-link-source"}, html: BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.RICH_LINK[i].LINK)}}).hostname});
						}

						var linkDesc = null;
						if (attach.RICH_LINK[i].DESC)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-rich-link-desc"}, html: attach.RICH_LINK[i].DESC});
						}

						var linkPreview = null;
						if (attach.RICH_LINK[i].HTML)
						{
							linkPreview = BX.create("div", { props : { className: "bx-messenger-attach-rich-link-html"}, html: attach.RICH_LINK[i].HTML});
							var link = BX.create("span", {props : { className: "bx-messenger-attach-rich-link"+(attach.RICH_LINK[i].PREVIEW? " bx-messenger-attach-rich-link-with-preview": "")}, children: [linkTitle, linkDesc, linkPreview]})
						}
						else if (attach.RICH_LINK[i].PREVIEW)
						{
							linkPreview = BX.create("span", { props : { className: "bx-messenger-file-image-src"}, children: [
								BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.RICH_LINK[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this, true)"}, props : { className: "bx-messenger-file-image-text"}}),
							]});
							var link = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.RICH_LINK[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-file-image"}, children: [
								linkPreview,
								BX.create("span", {props : { className: "bx-messenger-attach-rich-link-panel"}, children: [linkTitle, linkDesc, linkSource]})
							]});
						}
						else
						{
							var link = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.RICH_LINK[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-file-image bx-messenger-file-image-without-preview"}, children: [
								BX.create("span", {props : { className: "bx-messenger-attach-rich-link-panel"}, children: [linkTitle, linkDesc, linkSource]})
							]});
						}
						linkNodes.push(link);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-rich-links"}, children: linkNodes});
				}
				else if(attach.MESSAGE && attach.MESSAGE.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: this.decodeBbCode(attach.MESSAGE)});
				}
				else if(attach.HTML && attach.HTML.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: attach.HTML});
				}
				else if(attach.GRID && attach.GRID.length > 0)
				{
					var gridNodes = [];
					for (var i = 0; i < attach.GRID.length; i++)
					{
						var gridValue = this.decodeBbCode(attach.GRID[i].VALUE);
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
							gridValue = '<a href="'+attach.GRID[i].LINK+'" target="_blank">'+gridValue+'</a>';
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
							gridNode = BX.create("span", { props : { className: "bx-messenger-attach-block bx-messenger-attach-block-"+(attach.GRID[i].DISPLAY.toLowerCase())+" bx-messenger-attach-block-spoiler"}, attrs: { style: attach.GRID[i].DISPLAY == 'LINE'? width: ''}, children: [
								BX.create("div", { props : { className: "bx-messenger-attach-block-name"}, attrs: { style: attach.GRID[i].DISPLAY == 'ROW'? width: ''}, children: [
									BX.create("span", {props : { className: "bx-messenger-attach-block-spoiler-name"}, html: attach.GRID[i].NAME}),
									BX.create("span", {props : { className: "bx-messenger-attach-block-spoiler-icon"}})
								]}),
								BX.create("div", { props : { className: "bx-messenger-attach-block-value"}, attrs: { style: height+(attach.GRID[i].COLOR? 'color: '+attach.GRID[i].COLOR: ''), 'data-min-height': attach.GRID[i].HEIGHT, 'data-max-height': maxHeight}, children: [
									BX.create("span", {html: gridValue})
								]})
							]});
						}
						else
						{
							gridNode = BX.create("span", { props : { className: "bx-messenger-attach-block bx-messenger-attach-block-"+(attach.GRID[i].DISPLAY.toLowerCase())}, attrs: { style: attach.GRID[i].DISPLAY == 'LINE'? width: ''}, children: [
								BX.create("div", { props : { className: "bx-messenger-attach-block-name"}, attrs: { style: attach.GRID[i].DISPLAY == 'ROW'? width: ''}, html: attach.GRID[i].NAME}),
								BX.create("div", { props : { className: "bx-messenger-attach-block-value"}, attrs: { style: (attach.GRID[i].COLOR? 'color: '+attach.GRID[i].COLOR: '')}, html: gridValue})
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
						var imageNode = BX.create("a", { props : { className: "bx-messenger-file-image-src"}, attrs: {'href': BX.util.htmlspecialcharsback(attach.IMAGE[i].LINK), 'target': '_blank', 'title': attach.IMAGE[i].NAME}, children: [
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
							BX.create("span", { props : { className: "bx-messenger-file-title-name"}, html: fileName})
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

		var urlContext = this.isMobile()? 'mobile': (this.isDesktop() || this.BXIM.context == 'LINES'? 'desktop': 'default');
		var enableLink = true;
		var nodeCollection = [];

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
			if (file.type == 'image' && (file.preview || file.urlPreview[urlContext]))
			{
				var imageNodeMobile = null;
				if (this.isMobile() && file.preview && typeof(file.preview) != 'string')
				{
					if (file.urlPreview[urlContext])
					{
						var imageNodeMobile = BX.create("div", { attrs:{'src': file.urlPreview[urlContext]}, props : { className: "bx-messenger-file-image-text bx-messenger-hide"}});
					}
				}
				var imageNode = null;
				if (file.preview && typeof(file.preview) != 'string')
				{
					imageNode = file.preview;
					if (file.urlPreview[urlContext])
					{
						file.preview = '';
					}
				}
				else
				{
					imageNode = BX.create("img", {
						attrs:{
							'src': file.urlPreview[urlContext]? file.urlPreview[urlContext]: file.preview,
							'height': !file.image || file.image.height > 500? '500': file.image.height
						},
						props : { className: "bx-messenger-file-image-text"},
						events: { load: function(){
							this.parentNode.style.background = "#fff";
							this.removeAttribute('height');
						}}
					});
				}

				if (enableLink && file.urlShow[urlContext])
				{
					if (this.isMobile() && file.urlPreview[urlContext])
					{
						preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
								BX.create("span", {events: {click: BX.delegate(function(){
									this.BXIM.messenger.openPhotoGallery(file.urlPreview[urlContext]);
								}, this)}, props : { className: "bx-messenger-file-image-src"},  children: [
									imageNodeMobile,
									imageNode
								]})
							]})
						]});
					}
					else
					{
						preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
								BX.create("a", {attrs: {'href': file.urlShow[urlContext], 'target': '_blank'}, props : { className: "bx-messenger-file-image-src"},  children: [
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
			var title = BX.create("span", { attrs: {'title': file.name}, props : { className: "bx-messenger-file-title"}, children: [
				BX.create("span", { props : { className: "bx-messenger-file-title-name"}, html: fileName})
			]});
			if (enableLink && (file.urlShow[urlContext] || file.urlDownload[urlContext]))
			{
				if (this.isMobile())
					title = BX.create("span", { props : { className: "bx-messenger-file-title-href"}, events: {click: function(){ BX.localStorage.set('impmh', true, 1);  app.openDocument({url: file.urlDownload['mobile'], filename: fileName}) }}, children: [title]});
				else
					title = BX.create("a", { props : { className: "bx-messenger-file-title-href"}, attrs: {'href': file.urlShow? file.urlShow[urlContext]: file.urlDownload[urlContext], 'target': '_blank'}, children: [title]});
			}
			title = BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
				title,
				BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(file.size)}),
			]});

			var status = null;
			if (file.status == 'done')
			{
				if (!this.isMobile())
				{
					status = BX.create("div", { props : { className: "bx-messenger-file-download"}, children: [
						!file.urlDownload || !enableLink? null: BX.create("a", {attrs: {'href': file.urlDownload[urlContext], 'target': '_blank'}, props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"}, html: BX.message('IM_F_DOWNLOAD')}),
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
			'tempId': id,
			'chatId': chatId,
			'date': new Date(),
			'type': file.isImage? 'image': 'file',
			'preview': file.isImage? file.canvas: '',
			'name': file.name,
			'size': file.file.size,
			'status': 'upload',
			'progress': -1,
			'authorId': this.BXIM.userId,
			'authorName': this.BXIM.messenger.users[this.BXIM.userId].name,
			'urlPreview': '',
			'urlShow': '',
			'urlDownload': ''
		};

		if (!this.BXIM.disk.filesRegister[chatId])
			this.BXIM.disk.filesRegister[chatId] = {};

		this.BXIM.disk.filesRegister[chatId][id] = {
			'id': id,
			'type': this.BXIM.disk.files[chatId][id].type,
			'mimeType': file.file.type,
			'name': this.BXIM.disk.files[chatId][id].name,
			'size': this.BXIM.disk.files[chatId][id].size
		};

		this.diskChatDialogFileRegister(chatId, agent.messageText);
	}

	MessengerCommon.prototype.diskChatDialogFileRegister = function(chatId, text)
	{
		text = text || '';

		clearTimeout(this.BXIM.disk.timeout[chatId]);
		this.BXIM.disk.timeout[chatId] = setTimeout(BX.delegate(function(){
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

			var paramsFileId = []
			for (var id in this.BXIM.disk.filesRegister[chatId])
			{
				paramsFileId.push(id);
			}
			var tmpMessageId = 'tempFile'+this.BXIM.disk.fileTmpId;
			this.BXIM.messenger.message[tmpMessageId] = {
				'id': tmpMessageId,
				'chatId': chatId,
				'senderId': this.BXIM.userId,
				'recipientId': recipientId,
				'date': new Date(),
				'text': BX.MessengerCommon.prepareText(text, true),
				'params': {'FILE_ID': paramsFileId, 'CLASS': olSilentMode == "Y"? "bx-messenger-content-item-system": ""}
			};
			if (!this.BXIM.messenger.showMessage[recipientId])
				this.BXIM.messenger.showMessage[recipientId] = [];

			this.BXIM.messenger.showMessage[recipientId].push(tmpMessageId);
			BX.MessengerCommon.drawMessage(recipientId, this.BXIM.messenger.message[tmpMessageId]);
			BX.MessengerCommon.drawProgessMessage(tmpMessageId);

			this.recentListAdd({
				'id': tmpMessageId,
				'date': new Date(),
				'skipDateCheck': true,
				'recipientId': recipientId,
				'senderId': this.BXIM.userId,
				'text': text? text: '['+BX.message('IM_F_FILE')+']',
				'userId': recipientId,
				'userIsChat': recipientId.toString().substr(0,4) == 'chat',
				'params': {}
			}, true);

			this.BXIM.messenger.sendMessageFlag++;
			this.BXIM.messenger.popupMessengerFileFormInput.setAttribute('disabled', true);

			this.BXIM.disk.OldBeforeUnload = window.onbeforeunload;
			window.onbeforeunload = function(){
				if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.tryConnectDelay) == 'function') // TODO change to right code in near future (e.shelenkov)
				{
					BX.PULL.tryConnectDelay();
				}
				return BX.message('IM_F_EFP')
			};

			BX.ajax({
				url: this.BXIM.pathToFileAjax+'?FILE_REGISTER&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				timeout: 30,
				data: {'IM_FILE_REGISTER' : 'Y', CHAT_ID: chatId, RECIPIENT_ID: recipientId, TEXT: text, MESSAGE_TMP_ID: tmpMessageId, FILES: JSON.stringify(this.BXIM.disk.filesRegister[chatId]), OL_SILENT: olSilentMode, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data) {
					if (data.ERROR != '')
					{
						this.BXIM.messenger.sendMessageFlag--;
						delete this.BXIM.messenger.message[tmpMessageId];
						BX.MessengerCommon.drawTab(recipientId);
						window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;

						this.BXIM.disk.filesRegister[chatId] = {};

						if (this.BXIM.disk.formAgents['imDialog']["clear"])
							this.BXIM.disk.formAgents['imDialog'].clear();

						return false;
					}

					this.BXIM.messenger.sendMessageFlag--;
					var messagefileId = [];
					var filesProgress = {};
					for(var tmpId in data.FILE_ID)
					{
						var newFile = data.FILE_ID[tmpId];

						delete this.BXIM.disk.filesRegister[data.CHAT_ID][newFile.TMP_ID];

						if (parseInt(newFile.FILE_ID) > 0)
						{
							filesProgress[newFile.TMP_ID] = newFile.FILE_ID;
							this.BXIM.disk.filesProgress[newFile.TMP_ID] = newFile.FILE_ID;
							this.BXIM.disk.filesMessage[newFile.TMP_ID] = data.MESSAGE_ID;

							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID] = {};

							for (var key in this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID])
								this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID][key] = this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID][key];

							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID]['id'] = newFile.FILE_ID;
							delete this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID];

							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID]['name'] = newFile.FILE_NAME;
							if (BX('im-file-'+newFile.TMP_ID))
							{
								BX('im-file-'+newFile.TMP_ID).setAttribute('data-fileId', newFile.FILE_ID);
								BX('im-file-'+newFile.TMP_ID).id = 'im-file-'+newFile.FILE_ID;
								BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, newFile.FILE_ID);
							}

							messagefileId.push(newFile.FILE_ID);
						}
						else
						{
							this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID]['status'] = 'error';
							BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, newFile.TMP_ID);
						}
					}

					this.BXIM.messenger.message[data.MESSAGE_ID] = BX.clone(this.BXIM.messenger.message[data.MESSAGE_TMP_ID]);
					this.BXIM.messenger.message[data.MESSAGE_ID]['id'] = data.MESSAGE_ID;
					this.BXIM.messenger.message[data.MESSAGE_ID]['params']['FILE_ID'] = messagefileId;
					if (data.MESSAGE_TEXT)
					{
						this.BXIM.messenger.message[data.MESSAGE_ID]['text'] = data.MESSAGE_TEXT;
					}

					if (this.BXIM.messenger.popupMessengerLastMessage == data.MESSAGE_TMP_ID)
						this.BXIM.messenger.popupMessengerLastMessage = data.MESSAGE_ID;

					delete this.BXIM.messenger.message[data.MESSAGE_TMP_ID];

					var idx = BX.util.array_search(''+data.MESSAGE_TMP_ID+'', this.BXIM.messenger.showMessage[data.RECIPIENT_ID]);
					if (this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx])
						this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx] = ''+data.MESSAGE_ID+'';

					if (BX('im-message-'+data.MESSAGE_TMP_ID))
					{
						if (data.MESSAGE_TEXT)
						{
							BX('im-message-'+data.MESSAGE_TMP_ID).innerHTML = data.MESSAGE_TEXT;
						}
						BX('im-message-'+data.MESSAGE_TMP_ID).id = 'im-message-'+data.MESSAGE_ID;
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.MESSAGE_TMP_ID}}, true);
						if (element)
						{
							element.setAttribute('data-messageid',	''+data.MESSAGE_ID+'');
							if (element.getAttribute('data-blockmessageid') == ''+data.MESSAGE_TMP_ID)
								element.setAttribute('data-blockmessageid',	''+data.MESSAGE_ID+'');
						}
						else
						{
							var element2 = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+data.MESSAGE_TMP_ID}}, true);
							if (element2)
							{
								element2.setAttribute('data-blockmessageid', ''+data.MESSAGE_ID+'');
							}
						}
						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
							lastMessageElementDate.innerHTML = BX.MessengerCommon.formatDate(this.BXIM.messenger.message[data.MESSAGE_ID].date, BX.MessengerCommon.getDateFormatType('MESSAGE'));
					}
					BX.MessengerCommon.clearProgessMessage(data.MESSAGE_ID);

					if (this.BXIM.messenger.history[data.RECIPIENT_ID])
						this.BXIM.messenger.history[data.RECIPIENT_ID].push(data.MESSAGE_ID);
					else
						this.BXIM.messenger.history[data.RECIPIENT_ID] = [data.MESSAGE_ID];

					var olSilentMode = 'N';
					if (data.RECIPIENT_ID.toString().substr(0,4) == 'chat' && this.BXIM.messenger.linesSilentMode && this.BXIM.messenger.linesSilentMode[data.CHAT_ID])
					{
						olSilentMode = 'Y';
					}

					this.BXIM.messenger.popupMessengerFileFormRegChatId.value = data.CHAT_ID;
					this.BXIM.messenger.popupMessengerFileFormRegMessageId.value = data.MESSAGE_ID;
					this.BXIM.messenger.popupMessengerFileFormRegMessageHidden.value = olSilentMode;
					this.BXIM.messenger.popupMessengerFileFormRegParams.value = JSON.stringify(filesProgress);

					this.BXIM.disk.formAgents['imDialog'].submit();

					this.BXIM.messenger.popupMessengerFileFormInput.removeAttribute('disabled');
				}, this),
				onfailure: BX.delegate(function(){
					this.BXIM.messenger.sendMessageFlag--;
					delete this.BXIM.messenger.message[tmpMessageId];
					this.BXIM.disk.filesRegister[chatId] = {};

					BX.MessengerCommon.drawTab(recipientId);
					window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;

					if (this.BXIM.disk.formAgents['imDialog']["clear"])
						this.BXIM.disk.formAgents['imDialog'].clear();

				}, this)
			});
			this.BXIM.disk.fileTmpId++;
		}, this), 500);
	}

	MessengerCommon.prototype.diskChatDialogFileStart = function(status, percent, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[status.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].progress = parseInt(percent);
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
	}

	MessengerCommon.prototype.diskChatDialogFileProgress = function(status, percent, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[status.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].progress = parseInt(percent);
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
	}

	MessengerCommon.prototype.diskChatDialogFileDone = function(status, file, agent, pIndex)
	{
		if (!this.BXIM.disk.files[file.file.fileChatId][file.file.fileId])
			return false;

		if (this.BXIM.disk.files[file.file.fileChatId] && this.BXIM.disk.files[file.file.fileChatId][file.file.fileId])
		{
			file.file.fileParams['preview'] = this.BXIM.disk.files[file.file.fileChatId][file.file.fileId]['preview'];
		}
		if (!this.BXIM.disk.files[file.file.fileChatId])
			this.BXIM.disk.files[file.file.fileChatId] = {};

		file.file.fileParams.date = new Date(file.file.fileParams.date);
		this.BXIM.disk.files[file.file.fileChatId][file.file.fileId] = file.file.fileParams;
		BX.MessengerCommon.diskRedrawFile(file.file.fileChatId, file.file.fileId);

		delete this.BXIM.disk.filesMessage[file.file.fileTmpId];
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
	}

	MessengerCommon.prototype.diskChatDialogFileError = function(item, file, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[item.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		item.deleteFile();

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].status = "error";
		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].errorText = file.error;
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
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
			if (this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID])
			{
				delete this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID][tmpId];
				delete this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID][files[tmpId]];
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
		BX.ajax({
			url: this.BXIM.pathToFileAjax+'?FILE_UNREGISTER&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_FILE_UNREGISTER' : 'Y', CHAT_ID: stream.post.REG_CHAT_ID, FILES: stream.post.REG_PARAMS, MESSAGES: JSON.stringify(messages), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
		BX.MessengerCommon.drawTab(this.getRecipientByChatId(stream.post.REG_CHAT_ID));
	}


	/* Section: Telephony */
	MessengerCommon.prototype.pullPhoneEvent = function()
	{
		var pullPhoneEventHandler =  BX.delegate(function(command,params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
				console.info('pull info: ', command, params);
			}

			if (command == 'invite')
			{
				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if(!this.BXIM.webrtc.phoneSupport())
					return false;

				if (this.BXIM.webrtc.callInit || this.BXIM.webrtc.callActive)
				{
					// todo: set and proceed busy status in b_voximplant_queue
					/*BX.MessengerCommon.phoneCommand('busy', {'CALL_ID' : params.callId});*/
					return false;
				}

				if (BX.localStorage.get('viInitedCall') || BX.localStorage.get('viExternalCard'))
				{
					return false;
				}

				if (this.isMobile() || this.isDesktop() || !this.BXIM.desktopStatus)
				{
					if (params.CRM && params.CRM.FOUND)
					{
						this.BXIM.webrtc.phoneCrm = params.CRM;
					}
					else
					{
						this.BXIM.webrtc.phoneCrm = {};
					}

					this.BXIM.webrtc.phonePortalCall = params.portalCall? true: false;
					if (this.BXIM.webrtc.phonePortalCall && params.portalCallData)
					{
						for (var i in params.portalCallData.users)
						{
							params.portalCallData.users[i].last_activity_date = new Date(params.portalCallData.users[i].last_activity_date);
							params.portalCallData.users[i].mobile_last_date = new Date(params.portalCallData.users[i].mobile_last_date);
							params.portalCallData.users[i].idle = params.portalCallData.users[i].idle? new Date(params.portalCallData.users[i].idle): false;
							params.portalCallData.users[i].absent = params.portalCallData.users[i].absent? new Date(params.portalCallData.users[i].absent): false;

							this.BXIM.messenger.users[i] = params.portalCallData.users[i];
						}

						for (var i in params.portalCallData.hrphoto)
							this.BXIM.messenger.hrphoto[i] = params.portalCallData.hrphoto[i];

						params.callerId = this.BXIM.messenger.users[params.portalCallUserId].name;
						params.phoneNumber = '';

						if (this.isMobile())
						{
							this.BXIM.webrtc.phoneCrm.FOUND = 'Y';
							this.BXIM.webrtc.phoneCrm.CONTACT = {
								'NAME': params.portalCallData.users[params.portalCallUserId].name,
								'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
							};
						}
					}

					this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
					this.BXIM.webrtc.phoneCallTime = 0;

					this.BXIM.repeatSound('ringtone', 5000);

					if (this.isPage())
					{
						BX.MessengerWindow.changeTab('im');
					}

					BX.MessengerCommon.phoneCommand('wait', {'CALL_ID' : params.callId, 'DEBUG_INFO': this.getDebugInfo()});

					this.BXIM.webrtc.phoneIncomingWait({
						chatId: params.chatId,
						callId: params.callId,
						callerId: params.callerId,
						lineNumber: params.lineNumber,
						companyPhoneNumber: params.phoneNumber,
						isCallback: params.isCallback,
						showCrmCard: params.showCrmCard,
						crmEntityType: params.crmEntityType,
						crmEntityId: params.crmEntityId,
						crmActivityId: params.crmActivityId,
						crmActivityEditUrl: params.crmActivityEditUrl,
						portalCall: params.portalCall,
						portalCallUserId: params.portalCallUserId,
						portalCallData: params.portalCallData,
						config: params.config
					});

				}
				/*if (!this.isMobile() && this.isDesktop() && !this.BXIM.isFocus('all'))
				{
					var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {},  'phoneCrm': params.CRM};
					this.BXIM.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.phoneIncomingWaitDesktop("+params.chatId+",'"+params.callId+"', '"+params.callerId+"', '"+params.phoneNumber+"', true);", data, 'im-desktop-call');
				}*/
			}
			else if (command == 'answer_self')
			{
				if (this.BXIM.webrtc.callSelfDisabled || this.BXIM.webrtc.phoneCallId != params.callId)
					return false;

				this.BXIM.stopRepeatSound('ringtone');
				this.BXIM.stopRepeatSound('dialtone');

				this.BXIM.webrtc.callInit = false;
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort();
				if(this.isMobile())
				{
					this.BXIM.webrtc.callOverlayClose();
				}
				else
				{
					this.BXIM.webrtc.phoneCallView.close();
				}

				this.BXIM.webrtc.callInit = true;
				this.BXIM.webrtc.phoneCallId = params.callId;
			}
			else if (command == 'timeout')
			{
				if (this.BXIM.webrtc.phoneCallId != params.callId)
					return false;

				clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
				BX.localStorage.remove('viInitedCall');

				var external = this.BXIM.webrtc.phoneCallExternal;

				this.BXIM.stopRepeatSound('ringtone');
				this.BXIM.stopRepeatSound('dialtone');

				this.BXIM.webrtc.callInit = false;

				var phoneNumber = this.BXIM.webrtc.phoneNumber;
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort();

				if(this.BXIM.webrtc.phoneCallView)
				{
					this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle, {failedCode: params.failedCode});
				}

				if (external && params.failedCode == 486)
				{
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayProgress('offline');
						this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_ERROR_BUSY_PHONE'));
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.CALLBACK);
					}
					else if (this.BXIM.webrtc.phoneCallView)
					{
						this.BXIM.webrtc.phoneCallView.setProgress('offline');
						this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_ERROR_BUSY_PHONE'));
						this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.sipPhoneError);
					}
				}
				else if (external && params.failedCode == 480)
				{
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayProgress('error');
						this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_ERROR_NA_PHONE'));
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
					}
					else if (this.BXIM.webrtc.phoneCallView)
					{
						this.BXIM.webrtc.phoneCallView.setProgress('error');
						this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_ERROR_NA_PHONE'));
						this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.sipPhoneError);
					}
				}
				else
				{
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayProgress('error');
						this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_DECLINE'));
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
					}
					else if (this.BXIM.webrtc.phoneCallView)
					{
						if(this.BXIM.webrtc.isCallListMode())
						{
							this.BXIM.webrtc.phoneCallView.setStatusText('');
							this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.outgoing);
						}
						else
						{
							this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_END'));
							this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.idle);
							this.BXIM.webrtc.phoneCallView.autoClose();
						}
					}
				}
			}
			else if (command == 'outgoing')
			{
				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if (!this.isMobile() && this.BXIM.desktopStatus && !this.isDesktop())
					return false;

				this.BXIM.webrtc.phoneCallDevice = params.callDevice == 'PHONE'? 'PHONE': 'WEBRTC';
				this.BXIM.webrtc.phonePortalCall = params.portalCall? true: false;
				if (this.BXIM.webrtc.callInit && (this.BXIM.webrtc.phoneNumber == params.phoneNumber || params.phoneNumber.indexOf(this.BXIM.webrtc.phoneNumber) >= 0))
				{
					this.BXIM.webrtc.phoneNumber = params.phoneNumber;
					if (params.external && this.BXIM.webrtc.phoneCallId == params.callIdTmp || !this.BXIM.webrtc.phoneCallId)
					{
						this.BXIM.webrtc.phoneCallExternal = params.external? true: false;

						if (this.BXIM.webrtc.phoneCallExternal && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
						{
							this.BXIM.webrtc.phoneCallView.setProgress('connect');
							this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_WAIT_ANSWER'));
						}

						this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
						this.BXIM.webrtc.phoneCallId = params.callId;
						this.BXIM.webrtc.phoneCallTime = 0;
						this.BXIM.webrtc.phoneCrm = params.CRM;
						if(this.isMobile())
						{
							this.BXIM.webrtc.callOverlayDrawCrm();
						}
						else if(this.BXIM.webrtc.phoneCallView)
						{
							if (params.showCrmCard)
							{
								this.BXIM.webrtc.phoneCallView.setCrmData(params.CRM);
								this.BXIM.webrtc.phoneCallView.setCrmEntity({
									type: params.crmEntityType,
									id: params.crmEntityId,
									activityId: params.crmActivityId,
									activityEditUrl: params.crmActivityEditUrl
								});
								this.BXIM.webrtc.phoneCallView.setConfig(params.config);
								this.BXIM.webrtc.phoneCallView.setCallId(params.callId);
								if(params.lineNumber)
									this.BXIM.webrtc.phoneCallView.setLineNumber(params.lineNumber);

								if(params.lineName)
									this.BXIM.webrtc.phoneCallView.setCompanyPhoneNumber(params.lineName);

								this.BXIM.webrtc.phoneCallView.reloadCrmCard();
							}
						}
					}

					if (this.BXIM.webrtc.phonePortalCall && this.BXIM.messenger.users[params.portalCallUserId])
					{
						if (this.isMobile())
						{
							this.BXIM.webrtc.phoneCrm.FOUND = 'Y';
							this.BXIM.webrtc.phoneCrm.CONTACT = {
								'NAME': params.portalCallData.users[params.portalCallUserId].name,
								'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
							};
						}
						else if(this.BXIM.webrtc.phoneCallView)
						{
							this.BXIM.webrtc.phoneCallView.setPortalCall(true);
							this.BXIM.webrtc.phoneCallView.setPortalCallData(params.portalCallData);
							this.BXIM.webrtc.phoneCallView.setPortalCallUserId(params.portalCallUserId);
						}
					}

				}
				else if (!this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
				{
					this.BXIM.webrtc.phoneCallId = params.callId;
					this.BXIM.webrtc.phoneCallTime = 0;
					this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
					this.BXIM.webrtc.phoneCrm = params.CRM;

					this.BXIM.webrtc.phoneDisplayExternal({
						callId: params.callId,
						config: params.config? params.config: {},
						phoneNumber: params.phoneNumber,
						portalCall: params.portalCall,
						portalCallUserId: params.portalCallUserId,
						portalCallData: params.portalCallData,
						showCrmCard: params.showCrmCard,
						crmEntityType: params.crmEntityType,
						crmEntityId: params.crmEntityId
					});
				}
			}
			else if (command == 'start')
			{
				if (this.BXIM.webrtc.phoneCallId != params.callId)
					return false;

				this.BXIM.webrtc.callOverlayTimer('start');
				this.BXIM.stopRepeatSound('ringtone');
				if (this.BXIM.webrtc.phoneCallId == params.callId && this.BXIM.webrtc.phoneCallDevice == 'PHONE' && (this.BXIM.webrtc.phoneCallDevice == params.callDevice || this.BXIM.webrtc.phonePortalCall))
				{
					this.BXIM.webrtc.phoneOnCallConnected();
				}
				else if (this.BXIM.webrtc.phoneCallId == params.callId && params.callDevice == 'PHONE' && this.BXIM.webrtc.phoneIncoming)
				{
					this.BXIM.webrtc.phoneCallDevice = 'PHONE';
					if(this.BXIM.webrtc.phoneCallView)
					{
						this.BXIM.webrtc.phoneCallView.setDeviceCall(true);
					}
					this.BXIM.webrtc.phoneOnCallConnected();
				}
				if (params.CRM)
				{
					this.BXIM.webrtc.phoneCrm = params.CRM;
					this.BXIM.webrtc.callOverlayDrawCrm();
				}

				if (this.BXIM.webrtc.phoneNumber != '')
				{
					this.BXIM.webrtc.phoneNumberLast = this.BXIM.webrtc.phoneNumber;
					this.BXIM.setLocalConfig('phone_last', this.BXIM.webrtc.phoneNumber);
				}
			}
			else if (command == 'hold' || command == 'unhold')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					this.BXIM.webrtc.phoneHolded = command == 'hold';
				}
			}
			else if (command == 'update_crm')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId && params.CRM && params.CRM.FOUND)
				{
					this.BXIM.webrtc.phoneCrm = params.CRM;

					if(this.isMobile())
					{
						this.BXIM.webrtc.callOverlayDrawCrm();
						if (this.BXIM.webrtc.callNotify)
							this.BXIM.webrtc.callNotify.adjustPosition();
					}
					else if(this.BXIM.webrtc.phoneCallView)
					{
						this.BXIM.webrtc.phoneCallView.setCrmData(params.CRM);
						if(params.showCrmCard)
						{
							this.BXIM.webrtc.phoneCallView.setCrmEntity({
								type: params.crmEntityType,
								id: params.crmEntityId,
								activityId: params.crmActivityId,
								activityEditUrl: params.crmActivityEditUrl
							});
							this.BXIM.webrtc.phoneCallView.reloadCrmCard();
						}
					}
				}
			}
			else if (command == 'inviteTransfer')
			{
				if (this.isMobile()) // TODO MOBILE support transfer
					return false;

				if(!this.BXIM.webrtc.phoneSupport())
					return false;

				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if (this.BXIM.webrtc.callInit || this.BXIM.webrtc.callActive)
					return false;

				if (this.isDesktop() || !this.BXIM.desktopStatus)
				{
					if (params.CRM && params.CRM.FOUND)
					{
						this.BXIM.webrtc.phoneCrm = params.CRM;
					}
					this.BXIM.repeatSound('ringtone', 5000);
					BX.MessengerCommon.phoneCommand('waitTransfer', {'CALL_ID' : params.callId});

					this.BXIM.webrtc.phoneTransferEnabled = true;

					this.BXIM.webrtc.phoneIncomingWait({
						chatId: params.chatId,
						callId: params.callId,
						callerId: params.callerId,
						lineNumber: params.phoneNumber,
						companyPhoneNumber: params.phoneNumber,
						showCrmCard: params.showCrmCard,
						crmEntityType: params.crmEntityType,
						crmEntityId: params.crmEntityId,
						crmActivityId: params.crmActivityId,
						crmActivityEditUrl: params.crmActivityEditUrl,
						config: params.config,
					});
				}
				/*if (this.BXIM.desktop.ready() && !this.BXIM.isFocus('all'))
				{
					var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {},  'phoneCrm': params.CRM};
					this.BXIM.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.phoneIncomingWaitDesktop("+params.chatId+",'"+params.callId+"', '"+params.callerId+"');", data, 'im-desktop-call');
				}*/
			}
			else if (command == 'cancelTransfer' || command == 'timeoutTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId && !this.BXIM.webrtc.callSelfDisabled)
				{
					this.BXIM.webrtc.callInit = false;
					this.BXIM.stopRepeatSound('ringtone');
					this.BXIM.webrtc.phoneCallFinish();
					this.BXIM.webrtc.callAbort();
					if(this.isMobile())
					{
						this.BXIM.webrtc.callOverlayClose();
					}
					else
					{
						this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_END'));
						this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.idle);
						this.BXIM.webrtc.phoneCallView.autoClose();
					}
				}
			}
			else if (command == 'declineTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					this.BXIM.webrtc.errorInviteTransfer();
				}
			}
			else if (command == 'completeTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					if (params.transferUserId != this.BXIM.userId || this.isMobile())
					{
						this.BXIM.webrtc.successInviteTransfer();
					}
					else
					{
						this.BXIM.webrtc.phoneTransferEnabled = false;
						BX.localStorage.set('vite', false, 1);

						if (params.callDevice == 'PHONE')
						{
							this.BXIM.stopRepeatSound('ringtone');

							if (this.isMobile())
							{
								this.BXIM.messenger.openMessenger(this.BXIM.messenger.currentTab);
							}
							this.BXIM.webrtc.phoneCallDevice = 'PHONE';
							this.BXIM.webrtc.phoneOnCallConnected();
						}
						if (params.CRM)
						{
							this.BXIM.webrtc.phoneCrm = params.CRM;
							this.BXIM.webrtc.callOverlayDrawCrm();
						}
					}
				}
			}
			else if (command == 'phoneDeviceActive')
			{
				this.BXIM.webrtc.phoneDeviceActive = params.active == 'Y';
			}
			else if (command == 'changeDefaultLineId')
			{
				this.BXIM.webrtc.phoneDefaultLineId = params.defaultLineId;
			}
			else if (command == 'replaceCallerId')
			{
				var callTitle = BX.message('IM_PHONE_CALL_TRANSFER').replace('#PHONE#', params.callerId);
				this.BXIM.webrtc.setCallOverlayTitle(callTitle);
				if (params.CRM)
				{
					this.BXIM.webrtc.phoneCrm = params.CRM;
					if(this.isMobile())
					{
						this.BXIM.webrtc.callOverlayDrawCrm();
					}
					else if(this.BXIM.webrtc.phoneCallView)
					{
						this.BXIM.webrtc.phoneCallView.setCrmData(params.CRM);
						if(params.showCrmCard)
						{
							this.BXIM.webrtc.phoneCallView.setCrmEntity({
								type: params.crmEntityType,
								id: params.crmEntityId,
								activityId: params.crmActivityId,
								activityEditUrl: params.crmActivityEditUrl
							});
							this.BXIM.webrtc.phoneCallView.reloadCrmCard();
						}
					}
				}
			}
			else if (command == 'showExternalCall')
			{
				if (this.isMobile())
					return false;

				if (this.BXIM.webrtc.callInit || this.BXIM.webrtc.callActive)
					return false;

				if ( BX.localStorage.get('viInitedCall') || BX.localStorage.get('viExternalCard'))
				{
					return false;
				}

				if (this.isDesktop() || !this.BXIM.desktopStatus)
				{
					if (params.CRM && params.CRM.FOUND)
					{
						this.BXIM.webrtc.phoneCrm = params.CRM;
					}
					else
					{
						this.BXIM.webrtc.phoneCrm = {};
					}

					this.BXIM.webrtc.showExternalCall({
						callId: params.callId,
						fromUserId: params.fromUserId,
						toUserId: params.toUserId,
						isCallback: params.isCallback,
						phoneNumber: params.phoneNumber,
						lineNumber: params.lineNumber,
						companyPhoneNumber: params.companyPhoneNumber,
						showCrmCard: params.showCrmCard,
						crmEntityType: params.crmEntityType,
						crmEntityId: params.crmEntityId,
						crmActivityId: params.crmActivityId,
						crmActivityEditUrl: params.crmActivityEditUrl,
						config: params.config,
						portalCall: params.portalCall,
						portalCallData: params.portalCallData,
						portalCallUserId: params.portalCallUserId
					});
				}
			}
			else if (command == 'hideExternalCall')
			{
				if (this.isMobile())
					return false;

				if(this.BXIM.webrtc.callActive && this.BXIM.webrtc.phoneCallExternal && this.BXIM.webrtc.phoneCallId == params.callId)
				{
					this.BXIM.webrtc.hideExternalCall();
				}
			}
		}, this);
		if(this.isMobile())
		{
			BXMobileApp.addCustomEvent("onPull-voximplant", pullPhoneEventHandler);
		}
		else
		{
			BX.addCustomEvent("onPullEvent-voximplant",pullPhoneEventHandler);
		}
	}

	MessengerCommon.prototype.phoneCommand = function(command, params, async, successCallback)
	{
		if (!this.BXIM.webrtc.phoneSupport())
		return false;

		async = async != false;
		params = typeof(params) == 'object' ? params: {};

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_SHARED&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: async,
			data: {'IM_PHONE' : 'Y', 'COMMAND': command, 'PARAMS' : JSON.stringify(params), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: function(response)
			{
				if(BX.type.isFunction(successCallback))
				{
					successCallback(response)
				}
			}
		});

		return true;
	}

	MessengerCommon.prototype.phoneCorrect = function(number)
	{
		number = BX.util.trim(number.toString());

		if (number.substr(0, 2) == '+8' && number.length > 10)
		{
			number = '008'+number.substr(2);
		}
		number = number.replace(/[^0-9#*;,]/g, '');

		if (number.substr(0, 2) == '80' || number.substr(0, 2) == '81' || number.substr(0, 2) == '82')
		{
		}
		else if (number.substr(0, 2) == '00' && number.length >= 9)
		{
			number = number.substr(2);
		}
		else if (number.substr(0, 3) == '011' && number.length >= 10)
		{
			number = number.substr(3);
		}
		else if (number.substr(0, 1) == '8' && number.length >= 11)
		{
			number = '7'+number.substr(1);
		}
		else if (number.substr(0, 1) == '0' && number.length >= 8)
		{
			number = number.substr(1);
		}

		return number;
	}

	MessengerCommon.prototype.phoneOnIncomingCall = function(params)
	{
		if (this.BXIM.webrtc.phoneCurrentCall)
			return false;

		var viEvent = {};
		if (this.isMobile())
		{
			viEvent = BX.MobileVoximplantCall.events;
		}
		else
		{
			viEvent = VoxImplant.CallEvents;
		}

		this.BXIM.webrtc.phoneCurrentCall = params.call;
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Connected, BX.delegate(this.BXIM.webrtc.phoneOnCallConnected, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Disconnected, BX.delegate(this.BXIM.webrtc.phoneOnCallDisconnected, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Failed, BX.delegate(this.BXIM.webrtc.phoneOnCallFailed, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.answer();
	}

	MessengerCommon.prototype.phoneGetCallParams = function()
	{
		var result = BX.type.isPlainObject(this.BXIM.webrtc.phoneParams) ? BX.clone(this.BXIM.webrtc.phoneParams) : {};
		if (this.BXIM.webrtc.phoneFullNumber != this.BXIM.webrtc.phoneNumber)
		{
			result['FULL_NUMBER'] = this.BXIM.webrtc.phoneFullNumber;
		}
		return JSON.stringify(result);
	}

	MessengerCommon.prototype.phoneCallStart = function()
	{
		this.BXIM.webrtc.phoneParams['CALLER_ID'] = '';
		this.BXIM.webrtc.phoneParams['USER_ID'] = this.BXIM.userId;
		this.BXIM.webrtc.phoneLog('Call params: ', this.BXIM.webrtc.phoneNumber, this.BXIM.webrtc.phoneParams);
		if (!this.BXIM.webrtc.phoneAPI.connected())
		{
			this.BXIM.webrtc.phoneOnSDKReady();
			return false;
		}

		if (!this.isMobile() && false) // TODO debug mode for testing interface
		{
			this.BXIM.webrtc.phoneCurrentCall = true;
			this.BXIM.webrtc.callActive = true;
			this.BXIM.webrtc.phoneOnCallConnected();
			this.BXIM.webrtc.phoneCrm.FOUND = 'N';
			this.BXIM.webrtc.phoneCrm.CONTACT_URL = '#';
			this.BXIM.webrtc.phoneCrm.LEAD_URL = '#';
			this.BXIM.webrtc.callOverlayDrawCrm();
		}
		else
		{
			var viEvent = {};
			if (this.isMobile())
			{
				viEvent = BX.MobileVoximplantCall.events;
			}
			else
			{
				viEvent = VoxImplant.CallEvents;
				this.BXIM.webrtc.phoneAPI.setOperatorACDStatus('ONLINE');
			}

			this.BXIM.webrtc.phoneCurrentCall = this.BXIM.webrtc.phoneAPI.call(this.BXIM.webrtc.phoneNumber, false, this.phoneGetCallParams());
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Connected, BX.delegate(this.BXIM.webrtc.phoneOnCallConnected, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Disconnected, BX.delegate(this.BXIM.webrtc.phoneOnCallDisconnected, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Failed, BX.delegate(this.BXIM.webrtc.phoneOnCallFailed, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.ProgressToneStart, BX.delegate(this.BXIM.webrtc.phoneOnProgressToneStart, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.ProgressToneStop, BX.delegate(this.BXIM.webrtc.phoneOnProgressToneStop, this.BXIM.webrtc));
			if (this.isMobile())
			{
				this.BXIM.webrtc.phoneCurrentCall.start();
			}
		}

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_INIT&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_PHONE' : 'Y', 'COMMAND': 'init', 'NUMBER' : this.BXIM.webrtc.phoneNumber, 'NUMBER_USER' : BX.util.htmlspecialcharsback(this.BXIM.webrtc.phoneNumberUser), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				if (data.ERROR == '')
				{
					if (!(data.HR_PHOTO.length == 0))
					{
						for (var i in data.HR_PHOTO)
							this.BXIM.messenger.hrphoto[i] = data.HR_PHOTO[i];

						this.BXIM.webrtc.callOverlayUserId = data.DIALOG_ID;
					}
					else
					{
						this.BXIM.webrtc.callOverlayChatId = data.DIALOG_ID.substr(4);
					}
				}
			}, this)
		});
	}

	MessengerCommon.prototype.phoneCallFinish = function()
	{
		clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
		BX.localStorage.remove('viInitedCall');
		clearInterval(this.BXIM.webrtc.phoneCallTimeInterval);

		this.BXIM.webrtc.callOverlayTimer('pause');

		if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
		{
			BX.MessengerCommon.phoneCommand('deviceHungup', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
		else if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneTransferEnabled && this.BXIM.webrtc.phoneTransferUser == 0)
		{
			BX.MessengerCommon.phoneCommand('declineTransfer', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
		else if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneIncoming)
		{
			BX.MessengerCommon.phoneCommand('skip', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}

		if (!this.isMobile())
		{
			this.BXIM.desktop.closeTopmostWindow();
		}

		if (this.BXIM.webrtc.phoneCurrentCall)
		{
			try { this.BXIM.webrtc.phoneCurrentCall.hangup(); } catch (e) {}
			this.BXIM.webrtc.phoneCurrentCall = null;
			this.BXIM.webrtc.phoneLog('Call hangup call');
		}
		else if (this.BXIM.webrtc.phoneDisconnectAfterCallFlag && this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
		{
			setTimeout(BX.delegate(function(){
				if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
					this.BXIM.webrtc.phoneAPI.disconnect();
			}, this), 500)
		}

		if (this.isMobile())
		{}
		else
		{
			if (this.BXIM.webrtc.popupKeyPad)
				this.BXIM.webrtc.popupKeyPad.close();
			if (this.BXIM.webrtc.popupTransferDialog)
				this.BXIM.webrtc.popupTransferDialog.close();

			BX.localStorage.set('vite', false, 1);
		}

		this.BXIM.webrtc.phoneRinging = 0;
		this.BXIM.webrtc.phoneIncoming = false;
		this.BXIM.webrtc.phoneCallId = '';
		this.BXIM.webrtc.phoneCallExternal = false;
		this.BXIM.webrtc.phoneCallDevice = 'WEBRTC';
		//this.BXIM.webrtc.phonePortalCall = false;
		this.BXIM.webrtc.phoneNumber = '';
		this.BXIM.webrtc.phoneNumberUser = '';
		this.BXIM.webrtc.phoneParams = {};
		this.BXIM.webrtc.callOverlayOptions = {};
		//this.BXIM.webrtc.phoneCrm = {};
		this.BXIM.webrtc.phoneMicMuted = false;
		this.BXIM.webrtc.phoneHolded = false;
		this.BXIM.webrtc.phoneMicAccess = false;
		this.BXIM.webrtc.phoneTransferUser = 0;
		this.BXIM.webrtc.phoneTransferEnabled = false;
	}

	MessengerCommon.prototype.phoneAuthorize = function()
	{
		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_AUTHORIZE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_PHONE' : 'Y', 'COMMAND': 'authorize', 'UPDATE_INFO': this.BXIM.webrtc.phoneCheckBalance? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					this.BXIM.messenger.sendAjaxTry = 0;
					this.BXIM.webrtc.phoneCheckBalance = false;

					if (data.HR_PHOTO)
					{
						for (var i in data.HR_PHOTO)
							this.BXIM.messenger.hrphoto[i] = data.HR_PHOTO[i];
					}

					if (this.isMobile())
					{
						this.BXIM.webrtc.phoneLogin = data.LOGIN;
						this.BXIM.webrtc.phoneServer = data.SERVER;

						this.BXIM.webrtc.phoneLog('auth with', this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer);
						BX.MobileVoximplant.loginWithOneTimeKey(data.LOGIN+'@'+data.SERVER, data.HASH)
					}
					else
					{
						this.BXIM.webrtc.phoneLogin = data.LOGIN;
						this.BXIM.webrtc.phoneServer = data.SERVER;
					}
					this.BXIM.webrtc.phoneCallerID = data.CALLERID;

					this.BXIM.webrtc.phoneApiInit();
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR' && (this.isDesktop() || this.isMobile()) && this.BXIM.messenger.sendAjaxTry < 3)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function (){
						this.phoneAuthorize();
					}, this), 5000);

					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function(){
						this.phoneAuthorize();
					}, this), 2000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else
				{
					this.BXIM.webrtc.callOverlayDeleteEvents();
					this.BXIM.webrtc.callOverlayProgress('offline');

					this.BXIM.webrtc.phoneLog('onetimekey', data.ERROR, data.CODE);
					if (data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'SESSION_ERROR')
					{
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_401'));
					}
					else
					{
						this.BXIM.webrtc.callAbort(data.ERROR+(this.BXIM.webrtc.debug? '<br />('+BX.message('IM_ERROR_CODE')+': '+data.CODE+')': ''));
					}
					if (!this.isMobile())
					{
						this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
						this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
					}
				}
			}, this),
			onfailure: BX.delegate(function() {
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
			}, this)
		});
	}

	MessengerCommon.prototype.phoneOnAuthResult = function(e)
	{
		if (e.result)
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'PHONE')
				return false;

			this.BXIM.webrtc.phoneLog('Authorize result', 'success');
			if (this.BXIM.webrtc.phoneIncoming)
			{
				BX.MessengerCommon.phoneCommand((this.BXIM.webrtc.phoneTransferEnabled?'readyTransfer': 'ready'), {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
			else if (this.BXIM.webrtc.callInitUserId == this.BXIM.userId)
			{
				BX.MessengerCommon.phoneCallStart();
			}
		}
		else if (!this.isMobile() && e.code == 302)
		{
			BX.ajax({
				url: this.BXIM.pathToCallAjax+'?PHONE_ONETIMEKEY&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_PHONE' : 'Y', 'COMMAND': 'onetimekey', 'KEY': e.key, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (data.ERROR == '')
					{
						this.BXIM.webrtc.phoneLog('auth with', this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer);
						this.BXIM.webrtc.phoneAPI.loginWithOneTimeKey(this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer, data.HASH);
					}
					else
					{
						this.BXIM.webrtc.phoneCallFinish();

						this.BXIM.webrtc.phoneLog('onetimekey', data.ERROR, data.CODE);
						if (data.CODE)
							this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_ERROR_CONNECT'));
						else
							this.BXIM.webrtc.callAbort(data.ERROR+(this.debug? '<br />('+BX.message('IM_ERROR_CODE')+': '+data.CODE+')': ''));

						if (!this.isMobile())
						{
							this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
							this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
						}
					}
				}, this),
				onfailure: BX.delegate(function() {
					this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
					this.BXIM.webrtc.phoneCallFinish();
				}, this)
			});
		}
		else
		{
			if (e.code == 401 || e.code == 400 || e.code == 403 || e.code == 404 || e.code == 302)
			{
				this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_401'));
				this.BXIM.webrtc.phoneServer = '';
				this.BXIM.webrtc.phoneLogin = '';
				this.BXIM.webrtc.phoneCheckBalance = true;
				BX.MessengerCommon.phoneCommand('authorize_error');
			}
			else
			{
				this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
			}
			this.BXIM.webrtc.callOverlayProgress('offline');
			if (!this.isMobile())
			{
				this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
				this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
			}
			this.BXIM.webrtc.phoneCallFinish();
			this.BXIM.webrtc.phoneLog('Authorize result', 'failed', e.code);
			this.BXIM.webrtc.phoneServer = '';
			this.BXIM.webrtc.phoneLogin = '';
		}
	}

	MessengerCommon.prototype.phoneOnCallFailed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Call failed', e.code, e.reason);

		var reason = BX.message('IM_PHONE_END');
		if (e.code == 603)
		{
			reason = BX.message('IM_PHONE_DECLINE');
		}
		else if (e.code == 380)
		{
			reason = BX.message('IM_PHONE_ERR_SIP_LICENSE');
		}
		else if (e.code == 436)
		{
			reason = BX.message('IM_PHONE_ERR_NEED_RENT');
		}
		else if (e.code == 438)
		{
			reason = BX.message('IM_PHONE_ERR_BLOCK_RENT');
		}
		else if (e.code == 400)
		{
			reason = BX.message('IM_PHONE_ERR_LICENSE');
		}
		else if (e.code == 401)
		{
			reason = BX.message('IM_PHONE_401');
		}
		else if (e.code == 480 || e.code == 503)
		{
			if (this.BXIM.webrtc.phoneNumber == 911 || this.BXIM.webrtc.phoneNumber == 112)
			{
				reason = BX.message('IM_PHONE_NO_EMERGENCY');
			}
			else
			{
				reason = BX.message('IM_PHONE_UNAVAILABLE');
			}
		}
		else if (e.code == 484 || e.code == 404)
		{
			if (this.BXIM.webrtc.phoneNumber == 911 || this.BXIM.webrtc.phoneNumber == 112)
			{
				reason = BX.message('IM_PHONE_NO_EMERGENCY');
			}
			else
			{
				reason = BX.message('IM_PHONE_INCOMPLETED');
			}
		}
		else if (e.code == 402)
		{
			reason = BX.message('IM_PHONE_NO_MONEY')+(this.BXIM.isAdmin? ' '+BX.message('IM_PHONE_PAY_URL_NEW'): '');
		}
		else if (e.code == 486 && this.BXIM.webrtc.phoneRinging > 1)
		{
			reason = BX.message('IM_M_CALL_ST_DECLINE');
		}
		else if (e.code == 486)
		{
			reason = BX.message('IM_PHONE_ERROR_BUSY');
		}
		else if (e.code == 403)
		{
			reason = BX.message('IM_PHONE_403');
			this.BXIM.webrtc.phoneServer = '';
			this.BXIM.webrtc.phoneLogin = '';
			this.BXIM.webrtc.phoneCheckBalance = true;
		}

		this.BXIM.webrtc.phoneCallFinish();
		if (e.code == 408 || e.code == 403)
		{
			if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
			{
				setTimeout(BX.delegate(function(){
					if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
						this.BXIM.webrtc.phoneAPI.disconnect();
				}, this), 500)
			}
		}
		this.BXIM.webrtc.callOverlayProgress('offline');
		this.BXIM.webrtc.callAbort(reason);

		if (!this.isMobile())
		{
			this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
			this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
		}
	}

	MessengerCommon.prototype.phoneOnCallDisconnected = function(e)
	{
		this.BXIM.webrtc.phoneLog('Call disconnected', this.BXIM.webrtc.phoneCurrentCall? this.BXIM.webrtc.phoneCurrentCall.id(): '-', this.BXIM.webrtc.phoneCurrentCall? this.BXIM.webrtc.phoneCurrentCall.state(): '-');

		if (this.BXIM.webrtc.phoneCurrentCall)
		{
			this.BXIM.webrtc.phoneCallFinish();
			this.BXIM.webrtc.callOverlayDeleteEvents();
			this.BXIM.webrtc.callOverlayStatus(BX.message('IM_M_CALL_ST_END'));

			if (this.isMobile())
			{
				this.BXIM.webrtc.callOverlayProgress('offline');
				this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
			}
			else
			{
				this.BXIM.playSound('stop');
				this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
				if(this.BXIM.webrtc.isCallListMode())
				{
					this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.outgoing);
				}
				else
				{
					this.BXIM.webrtc.phoneCallView.setStatusText(BX.message('IM_PHONE_END'));
					this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.idle);
					this.BXIM.webrtc.phoneCallView.autoClose();
				}
			}
		}

		if (this.BXIM.webrtc.phoneDisconnectAfterCallFlag && this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
		{
			setTimeout(BX.delegate(function(){
				if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
					this.BXIM.webrtc.phoneAPI.disconnect();
			}, this), 500)
		}
	}

	MessengerCommon.prototype.phoneOnProgressToneStart = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;

		this.BXIM.webrtc.phoneLog('Progress tone start', this.BXIM.webrtc.phoneCurrentCall.id());
		this.BXIM.webrtc.phoneRinging++;
		this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_WAIT_ANSWER'));
	}

	MessengerCommon.prototype.phoneOnProgressToneStop = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;
		this.BXIM.webrtc.phoneLog('Progress tone stop', this.BXIM.webrtc.phoneCurrentCall.id());
	}

	MessengerCommon.prototype.phoneOnConnectionEstablished = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection established', this.BXIM.webrtc.phoneAPI.connected());
	}

	MessengerCommon.prototype.phoneOnConnectionFailed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection failed');
		this.BXIM.webrtc.phoneCallFinish();
		this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
	}

	MessengerCommon.prototype.phoneOnConnectionClosed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection closed');
		this.BXIM.webrtc.phoneSDKinit = false;
	}

	MessengerCommon.prototype.phoneOnMicResult = function(e)
	{
		this.BXIM.webrtc.phoneMicAccess = e.result;
		this.BXIM.webrtc.phoneLog('Mic Access Allowed', e.result);

		if (!this.isMobile())
		{
			clearTimeout(this.BXIM.webrtc.callDialogAllowTimeout);
			if (this.BXIM.webrtc.callDialogAllow)
				this.BXIM.webrtc.callDialogAllow.close();
		}

		if (e.result)
		{
			this.BXIM.webrtc.callOverlayProgress('connect');
			this.BXIM.webrtc.callOverlayStatus(BX.message('IM_M_CALL_ST_CONNECT'));
		}
		else
		{
			this.BXIM.webrtc.phoneCallFinish();
			this.BXIM.webrtc.callOverlayProgress('offline');
			this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS'));
			if (!this.isMobile())
			{
				this.BXIM.webrtc.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
				this.BXIM.webrtc.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
			}
		}
	}

	MessengerCommon.prototype.phoneOnNetStatsReceived = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall || this.BXIM.webrtc.phoneCurrentCall.state() != "CONNECTED")
			return false;

		var percent = (100-parseInt(e.stats.packetLoss));
		var grade = this.BXIM.webrtc.callPhoneOverlayMeter(percent);

		this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'meter', 'PACKETLOSS': e.stats.packetLoss, 'PERCENT': percent, 'GRADE': grade}));
	}

	MessengerCommon.prototype.phoneHold = function()
	{
		if (!this.BXIM.webrtc.phoneCurrentCall && this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			return false;

		this.BXIM.webrtc.phoneHolded = true;
		if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
		{
			this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'hold'}));
		}
		else
		{
			BX.MessengerCommon.phoneCommand('hold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
	}

	MessengerCommon.prototype.phoneUnhold = function()
	{
		if (!this.BXIM.webrtc.phoneCurrentCall && this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			return false;

		this.BXIM.webrtc.phoneHolded = false;
		if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
		{
			this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'unhold'}));
		}
		else
		{
			BX.MessengerCommon.phoneCommand('unhold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
	}

	MessengerCommon.prototype.phoneToggleHold = function(state)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall && this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			return false;

		if (typeof(state) != 'undefined')
		{
			this.BXIM.webrtc.phoneHolded = !state;
		}

		if (this.BXIM.webrtc.phoneHolded)
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			{
				this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'unhold'}));
			}
			else
			{
				BX.MessengerCommon.phoneCommand('unhold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
		}
		else
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			{
				this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'hold'}));
			}
			else
			{
				BX.MessengerCommon.phoneCommand('hold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
		}
		this.BXIM.webrtc.phoneHolded = !this.BXIM.webrtc.phoneHolded;
	}

	MessengerCommon.prototype.phoneSendDTMF = function(key)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;

		this.BXIM.webrtc.phoneLog('Send DTMF code', this.BXIM.webrtc.phoneCurrentCall.id(), key);

		this.BXIM.webrtc.phoneCurrentCall.sendTone(key);
	}

	MessengerCommon.prototype.phoneStartCallViaRestApp = function(number, lineId, params)
	{
		BX.rest.callMethod(
			'voximplant.call.startViaRest',
			{
				'NUMBER': number,
				'LINE_ID': lineId,
				'PARAMS': params,
				'SHOW': 'Y'
			}
		);
	}

	MessengerCommon.prototype.phoneGetCallFields = function(chatId)
	{
		if (!this.BXIM.messenger.chat[chatId] || this.BXIM.messenger.chat[chatId].type != "call")
			return {crm: false};

		var currentChat = this.BXIM.messenger.chat[chatId];

		var crmData = currentChat.entity_data_1.toString().split('|');
		if(crmData.length < 3 || crmData[0] !== 'Y')
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
						this.BXIM.messenger.message[i] = data.MESSAGE[i];
					}

					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
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

					this.BXIM.messenger.linesShowHistory(data.CHAT_ID, {'HISTORY': data.USERS_MESSAGE, 'FILES': data.FILES, 'CAN_JOIN': data.CAN_JOIN, 'CAN_VOTE_HEAD': data.CAN_VOTE_HEAD, 'SESSION_VOTE_HEAD': data.SESSION_VOTE_HEAD, 'SESSION_ID': data.SESSION_ID});
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
		if (
			!this.BXIM.messenger.message[messageId]
			|| this.BXIM.userId != this.BXIM.messenger.chat[this.BXIM.messenger.message[messageId].chatId].owner
		)
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
					if (params.SLIDER == 'Y')
					{
						this.BXIM.messenger.openMessengerSlider('chat'+data.CHAT_ID, params);
					}
					else
					{
						this.BXIM.messenger.openMessenger('chat'+data.CHAT_ID, params);
					}
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
			if (!this.BXIM.messenger.users[this.BXIM.userId].connector && !(lineSource == 'livechat' || lineSource == 'network'))
			{
				return null;
			}

			disableAction = !this.BXIM.messenger.users[this.BXIM.userId].connector;
		}
		else if (
			!this.BXIM.messenger.bot[this.BXIM.messenger.currentTab]
			|| this.BXIM.messenger.bot[this.BXIM.messenger.currentTab].type != 'network'
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
		var headResult = this.linesVoteHeadNodes(message.params.IMOL_VOTE_SID, message.params.IMOL_VOTE_HEAD, session.canVoteHead);

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
				]})
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
		if ((this.BXIM.messenger.message[messageId].date.getTime()/1000)+86400 < (new Date().getTime())/1000)
		{
			this.BXIM.openConfirm(BX.message('IM_OL_VOTE_END'));
			return false;
		}
		if (dialogId.toString().substr(0, 4) == 'chat')
		{
			if (!this.BXIM.messenger.users[this.BXIM.userId].connector)
			{
				return false;
			}
		}
		else if (
			!this.BXIM.messenger.bot[dialogId]
			|| this.BXIM.messenger.bot[dialogId].type != 'network'
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
			url: this.BXIM.pathToAjax+'?LINES_SAVE_TO_QUICK_ANSWERS&V='+this.BXIM.revision,
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

	MessengerCommon.prototype.linesVoteHeadSend = function(sessionId, rating)
	{
		sessionId = parseInt(sessionId);
		rating = parseInt(rating);

		if (sessionId <= 0 || rating <= 0 || rating > 5)
			return false;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_VOTE_SEND&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'voteHead', 'SESSION_ID' : sessionId, 'RATING' : rating, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		});

		return true;
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
		if (!this.BXIM.path.crm[entityType])
			return '';

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
		session.lineId = source[1];
		session.canVoteHead = this.linesCanVoteAsHead(source[1]);

		var sessionData = chatData.entity_data_1.toString().split('|');

		session.crm = typeof(sessionData[0]) != 'undefined' && sessionData[0] == 'Y'? 'Y': 'N';
		session.crmEntityType = typeof(sessionData[1]) != 'undefined'? sessionData[1]: 'NONE';
		session.crmEntityId = typeof(sessionData[2]) != 'undefined'? sessionData[2]: 0;
		session.crmLink = '';
		session.pin = typeof(sessionData[3]) != 'undefined' && sessionData[3] == 'Y'? 'Y': 'N';
		session.wait = typeof(sessionData[4]) != 'undefined' && sessionData[4] == 'Y'? 'Y': 'N';
		session.id = typeof(sessionData[5]) != 'undefined'? parseInt(sessionData[5]): Math.round(new Date()/1000)+chatData.id;
		session.dateCreate = typeof(sessionData[6]) != 'undefined' || sessionData[6] > 0? parseInt(sessionData[6]): session.id;

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

		this.BXIM.messenger.chat[chatId].entity_data_1 = [session.crm, session.crmEntityType, session.crmEntityId, session.pin, session.wait, session.id, session.dateCreate].join('|')

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
		if (!chatData || !(chatData.type == 'livechat' || chatData.type == 'lines'))
		{
			return sourceId;
		}

		if (chatData.type == 'livechat')
		{
			sourceId = 'livechat';
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
			url: this.BXIM.pathToAjax+'?LINES_ANSWER&V='+this.BXIM.revision,
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
			url: this.BXIM.pathToAjax+'?LINES_SKIP&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'skip', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
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
			url: this.BXIM.pathToAjax+'?LINES_ACTIVATE_PIN_MODE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'pinMode', 'ACTIVATE': flag, 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				BX.MessengerCommon.linesSetSession(chatId, {'pin': flag});
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesCloseDialog = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		if (!BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.MessengerCommon.dialogCloseCurrent();

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CLOSE_DIALOG&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'closeDialog', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
				BX.MessengerCommon.linesSetSession(chatId, {'wait': 'Y'});
				this.BXIM.messenger.redrawChatHeader({userRedraw: false});
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	MessengerCommon.prototype.linesMarkAsSpam = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_MARK_SPAM&V='+this.BXIM.revision,
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
	};

	MessengerCommon.prototype.linesInterceptSession = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].entity_type != 'LINES')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_INTERCEPT_SESSION&V='+this.BXIM.revision,
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
		if (session.crm == 'Y')
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?LINES_CREATE_LEAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'COMMAND': 'createLead', 'CHAT_ID' : chatId, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
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
		if (session.crm == 'N')
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
			url: this.BXIM.pathToAjax+'?LINES_CHANGE_CRM_ENTITY&V='+this.BXIM.revision,
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
			url: this.BXIM.pathToAjax+'?LINES_CANCEL_CRM_EXTEND&V='+this.BXIM.revision,
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

		BX.remove(BX('im-message-keyboard-'+messageId));
	}

	MessengerCommon.prototype.getMessagePlural = function(messageId, number)
	{
		var pluralForm, langId;

		langId = BX.message('LANGUAGE_ID') || 'en';
		number = parseInt(number);

		if (number < 0)
		{
			number = -1*number;
		}

		if (langId)
		{
			switch (langId)
			{
				case 'de':
				case 'en':
					pluralForm = ((number !== 1) ? 1 : 0);
				break;

				case 'ru':
				case 'ua':
					pluralForm = (((number%10 === 1) && (number%100 !== 11)) ? 0 : (((number%10 >= 2) && (number%10 <= 4) && ((number%100 < 10) || (number%100 >= 20))) ? 1 : 2));
				break;

				default:
					pluralForm = 1;
				break;
			}
		}
		else
		{
			pluralForm = 1;
		}

		return BX.message(messageId + '_PLURAL_' + pluralForm);
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
			this.BXIM.confirm(BX.message('IM_UNKNOWN_ERROR'));
		}
		return true;
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