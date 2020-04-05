if (typeof oForumForm != "object")	
	var oForumForm = {};

var MessageMax = 64000;


/***********************************************************/
var sonetChatRand = -1;
var sonetChatMsgs = "";
var sonetChatTout;
var sonetChatPtr = "";
var sonetChatFocus = true;
var sonetChatOldTitle = "";
var sonetChatNewTitle = "";
var sonetSoundOn = true;
var sonetChatSelection = false;
var sonetChatSelectionRange = false;

if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var sonetChatXmlHttpAdd = new XMLHttpRequest();
var sonetChatXmlHttpGet = new XMLHttpRequest();
var sonetChatXmlHttpOpt = new XMLHttpRequest();

jsUtils.addEvent(window, "load", sonet_chat_onload);
jsUtils.addEvent(window, "resize", sonet_chat_onresize);
jsUtils.addEvent(document, "keypress", sonet_chat_onkeypress);

if(jsUtils.IsIE())
{
	jsUtils.addEvent(document, "focusin", sonetSetFocus);
	jsUtils.addEvent(document, "focusout", sonetLostFocus);
}
else
{
	jsUtils.addEvent(window, "focus", sonetSetFocus);
	jsUtils.addEvent(window, "blur", sonetLostFocus);
}

function sonetSetFocus()
{
	if(!sonetChatFocus)
	{
		sonetChatFocus = true;
		sonetChatNewTitle = '';
		sonet_chat_settitle();
	}
}

function sonetLostFocus()
{
	sonetChatFocus = false;
}

function sonet_chat_msg_add()
{
	var textarea = document.getElementById('post_message_id');
	
	if (!textarea.value || sonetChatXmlHttpAdd.readyState % 4)
		return;
		
	var user_list = sonet_get_user_param();
	if(user_list == '')
		return;

	var saveText;

	SocNetShowWaitWindow();

	sonetChatRand += 1;

	sonetChatXmlHttpAdd.open("POST", sonetChatMsgAddPath, true);
	sonetChatXmlHttpAdd.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	sonetChatXmlHttpAdd.onreadystatechange = function()
	{
		if(sonetChatXmlHttpAdd.readyState == 4)
		{
			if(sonetChatXmlHttpAdd.status == 200)
			{
				if(jsUtils.trim(sonetChatXmlHttpAdd.responseText).length > 0)
				{
					sonetChatMsgs += '<br /><b>' + messageTrSystem + '</b><br />' + sonetChatXmlHttpAdd.responseText + '<br />';
					sonet_chat_out_msgs(true);
					sonet_play_sound('system');
					textarea.value = saveText;
				}
			}
			else
			{
				sonetChatMsgs += '<br /><b>' + messageTrSystem + '</b><br />' + messageNetworkError + '<br />';
				sonet_chat_out_msgs(true);
				sonet_play_sound('system');
				textarea.value = saveText;
			}
			document.getElementById('post_message_button').disabled = false;
			sonet_chat_msgs_get();
		}
	}

	sonetChatXmlHttpAdd.send("rand=" + sonetChatRand
		+ "&" + sonetChatSessid
		+ "&data=" + encodeURIComponent(textarea.value)
		+ user_list
	);

	saveText = textarea.value;
	textarea.value = '';
	document.getElementById('post_message_button').disabled = true;
	textarea.focus();
}

function sonet_chat_msgs_get(replyMesageId)
{
	clearTimeout(sonetChatTout);
	sonetChatTout = setTimeout("sonet_chat_msgs_get();", Math.round(1000 * sonetChatTimeout));

	var user_list;

	if (sonetChatXmlHttpGet.readyState % 4)
	{
		SocNetCloseWaitWindow();
		return;
	}
	
	sonetChatRand += 1;
	sonetChatXmlHttpGet.open(
		"get",
		sonetChatMsgGetPath + "?"
			+ "rand=" + sonetChatRand
			+ (parseInt(sonetChatUserId)>0? '&user_id='+sonetChatUserId : '')
			+ "&mptr=" + sonetChatPtr
			+ (replyMesageId > 0? '&message_id='+replyMesageId : '')
	);
	sonetChatXmlHttpGet.onreadystatechange = function()
	{
		if (sonetChatXmlHttpGet && sonetChatXmlHttpGet.readyState == 4 && sonetChatXmlHttpGet.status == 200)
		{
			SocNetCloseWaitWindow();

			if(jsUtils.trim(sonetChatXmlHttpGet.responseText) == '')
			{
				if(sonet_set_online_users([]))
					sonet_chat_out_msgs(false);
				return;
			}
				
			var data = sonet_chat_parse(sonetChatXmlHttpGet.responseText);
			if (data[0] == '*')
			{
//				sonet_chat_onload();
				return;
			}
			var bShowMess = false;
			var bShowNotice = false;
			var bSoundMessage = false;
			var bSoundError = false;
			var aUsersOnline = [];
			for (var i = 0; i < data.length; i++)
			{
				var command = data[i].substr(0, 1);
				if (command == '+')
				{
					var user_id = parseInt(data[i].substr(1), 10);
					if(user_id > 0)
						aUsersOnline[aUsersOnline.length] = user_id;
				}

				if (command == 'e')
				{
					sonetChatMsgs += '<br /><b>' + messageTrError + '</b><br />' + data[i + 1] + '<br />';
					bShowMess = true;
					bShowNotice = true;
					bSoundError = true;
					i += 1;
				}

				if (command == 'm')
				{
					sonetChatPtr = data[i + 1];

					if (data[i + 2].substr(0, 2) == "IN")
					{
						var user_id = parseInt(data[i].substr(1), 10);
						sonetChatUserId = parseInt(sonetChatUserId);
						if(sonetChatUserId > 0 && sonetChatUserId == user_id)
						{
							sonetChatMsgs += '<br /><span class="socnet-chat-incoming"><div class="socnet-chat-icon"></div><b>'+sonetChatUser+'</b><br style="clear:both;"/>' 
								+ '<span class="socnet-chat-date">' +sonet_chat_date(data[i + 1]) + '</span> ' + data[i + 3] + '</span><br />';
							bShowMess = true;
							bShowNotice = true;
							bSoundMessage = true;
						}
						else
						{
							var mess_id = parseInt(data[i+2].substr(2), 10);
							sonet_message_notify(user_id, mess_id);
						}
					}
					else
					{
						sonetChatMsgs += '<br /><span class="socnet-chat-outcoming"><div class="socnet-chat-icon"></div><b>'+sonetSelfUser+'</b><br style="clear:both;"/>' 
							+ '<span class="socnet-chat-date">' +sonet_chat_date(data[i + 1]) + '</span> ' + data[i + 3] + '</span><br />';
						bShowMess = true;
					}

					i += 3;
				}
			}
			var bOnlineMess = sonet_set_online_users(aUsersOnline);

			if(bShowMess || bOnlineMess)
				sonet_chat_out_msgs(bShowNotice);

			if(bSoundMessage)
				sonet_play_sound('message');
			else if(bSoundError)
				sonet_play_sound('system');
		}
	}
	sonetChatXmlHttpGet.send(null);
}

function sonet_get_user_param()
{
	var textarea = document.getElementById('post_message_id');
	var form = textarea.form;
	var user_id = '';

	if(form['USER_ID[]'])
	{
		if(form['USER_ID[]'].length > 1)
		{
			for(var i=0; i<form['USER_ID[]'].length; i++)
				if(form['USER_ID[]'][i].checked)
					user_id += '&user_id[]='+form['USER_ID[]'][i].value;
		}
		else if(form['USER_ID[]'].checked)
			user_id += '&user_id[]='+form['USER_ID[]'].value;
	}
	if(parseInt(sonetChatUserId)>0) 
		user_id += "&user_id[]=" + sonetChatUserId;
		
	return user_id;
}

function sonet_chat_out_msgs(bShowNotice)
{
	var o = document.getElementById('sonet_chat_messages');

	//if srollbar in the bottom, lets scroll
	var bScroll = (o.scrollHeight <= o.clientHeight || o.scrollTop > o.scrollHeight-o.clientHeight-10);

	o.innerHTML = sonetChatMsgs;

	if(bScroll)
		o.scrollTop = o.scrollHeight-o.clientHeight+100;
		
	if(!sonetChatFocus && bShowNotice)
	{
		sonetChatNewTitle = messageNewMessage;
		sonet_chat_settitle();
	}
}

function sonet_play_sound(snd)
{
	//no sound to focused window or sound disabled
	if(sonetChatFocus || !sonetSoundOn)
		return;

	//play sound
	var player = document.getElementById('socnet_player');
	if(!player)
		return;

	if(!player.sendEvent)
		player = document.getElementById('socnet_player_embed');

	var file;
	if(snd == 'system')
		file = '/bitrix/sounds/socialnetwork/system_message.mp3';
	else
		file = '/bitrix/sounds/socialnetwork/incoming_message.mp3';

	player.sendEvent('LOAD', file);
	player.sendEvent('PLAY');
}

function sonet_switch_sound(obj)
{
	var bSoundOn = (obj.className == 'socnet-button socnet-sound-on');
	obj.className = (bSoundOn? 'socnet-button socnet-sound-off' : 'socnet-button socnet-sound-on');
	obj.title = (bSoundOn? messSoundOff : messSoundOn);
	sonetSoundOn = !bSoundOn;

	sonet_save_option('socnet', 'chat', 'sound', (bSoundOn? 'N':'Y'));
}

function sonet_save_option(category, name, option, value)
{
	sonetChatXmlHttpOpt.open("get",	'/bitrix/admin/user_options.php?'+'&p[0][c]='+category+
		'&p[0][n]='+name+'&p[0][v]['+option+']='+value+'&'+sonetChatSessid);
	sonetChatXmlHttpOpt.send(null);
}

function sonet_load_last_char()
{
	sonetChatMsgs = "";
	sonetChatPtr = sonetChatLastDate;
	SocNetShowWaitWindow();
	sonet_chat_msgs_get();
	document.getElementById('post_message_id').focus();
}

function sonet_chat_onload()
{
	var el = document.getElementById('post_message_id');
	if(el)
	{
		el.focus();
		sonet_chat_reset();
	}
	sonet_chat_onresize();
}

function sonet_chat_onresize()
{
	var div0 = document.getElementById('socnet_chat_body');
	var div1 = document.getElementById('socnet_chat_info');
	var div2 = document.getElementById('sonet_chat_messages');
	if(!div2)
		return;
	var div3 = document.getElementById('socnet_chat_form');
	var div_selectors = document.getElementById('socnet_chat_selectors');
	var div_group = document.getElementById('socnet_user_list');
	div0.style.height = document.body.clientHeight + 'px';

	var d1 = jsUtils.GetStyleValue(div0, 'padding-top');
	var d2 = jsUtils.GetStyleValue(div0, 'padding-bottom');
	var d3 = jsUtils.GetStyleValue(div2, 'margin-top');
	var d4 = jsUtils.GetStyleValue(div3, 'margin-top');

	var body_margin = parseInt(d1? d1:jsUtils.GetStyleValue(div0, 'paddingTop')) +
		parseInt(d2? d2:jsUtils.GetStyleValue(div0, 'paddingBottom'));

	var h =  document.body.clientHeight - div1.offsetHeight - div3.offsetHeight 
		- body_margin
		- parseInt(d3? d3:jsUtils.GetStyleValue(div2, 'marginTop')) 
		- parseInt(d4? d4:jsUtils.GetStyleValue(div3, 'marginTop'));
	div2.style.height = (h < 30? 30:h) + 'px';
	
	div_group.style.height = document.body.clientHeight - body_margin - div_selectors.offsetHeight - 3 + 'px';
}

function sonet_chat_onkeypress(e)
{
	if(!e) e = window.event;
	if(!e) return;
	if(e.keyCode == 27)
		window.close();
}

function sonet_chat_reset()
{
	//d1 = new Date();
	//sonetChatPtr = d1.getFullYear() + "-" + sonet_chat_date_aux(d1.getMonth() + 1) + "-" + sonet_chat_date_aux(d1.getDate()) + " " + sonet_chat_date_aux(d1.getHours()) + ':' + sonet_chat_date_aux(d1.getMinutes()) + ':' + sonet_chat_date_aux(d1.getSeconds());
	sonetChatPtr = sonetChatNowDate;

	sonetChatMsgs = "";
	//sonetChatMsgs = "";

	clearTimeout(sonetChatTout);
	sonetChatXmlHttpAdd.abort();
	sonetChatXmlHttpGet.abort();

	SocNetShowWaitWindow();
	sonetChatTout = setTimeout("sonet_chat_msgs_get("+sonetChatReplyMesageId+");", 1);
}

function sonet_chat_parse(str)
{
	str = str.replace(/^\s+/, '');
	str = str.replace(/\s+$/, '');
	return str.split(/\r\n/);
}

function sonet_chat_date_aux(num)
{
	if (num < 10)
		return '0' + num;
	else
		return num;
}

function sonet_chat_date(dateString)
{
	d1 = new Date();

	var arDate = dateString.split(" ");
	var arDate1 = arDate[0].split("-");
	var arDate2 = arDate[1].split(":");
	d2 = new Date(arDate1[0], arDate1[1] - 1, arDate1[2], arDate2[0], arDate2[1], arDate2[2]);

	if ((d2.getDate() == d1.getDate()) && (d2.getMonth() == d1.getMonth()))
		return sonet_chat_date_aux(d2.getHours()) + ':' + sonet_chat_date_aux(d2.getMinutes()) + ':' + sonet_chat_date_aux(d2.getSeconds());
	else
		return sonet_chat_date_aux(d2.getDate()) + ' ' + mmTrMonth[d2.getMonth()] + ', ' + sonet_chat_date_aux(d2.getHours()) + ':' + sonet_chat_date_aux(d2.getMinutes()) + ':' + sonet_chat_date_aux(d2.getSeconds());
}

var sonetTitleTrigger = true;
function sonet_chat_settitle()
{
	if(sonetChatNewTitle != '')
	{
		if(sonetChatOldTitle == '')
			sonetChatOldTitle = document.title;

		if(sonetTitleTrigger)
			document.title = sonetChatNewTitle;
		else
			document.title = "*"+sonetChatNewTitle;

		sonetTitleTrigger = !sonetTitleTrigger;

		setTimeout("sonet_chat_settitle()", 1000);
	}
	else
	{
		if(sonetChatOldTitle != '' && document.title != sonetChatOldTitle)
			document.title = sonetChatOldTitle;
	}
}
/**********************************************************/

function SocNetWaitOnKeyPress(e)
{
	if(!e) e = window.event;
	if(!e) return;
	if(e.keyCode == 27)
		SocNetCloseWaitWindow();
}

function SocNetShowWaitWindow()
{
	SocNetCloseWaitWindow();

	var obWndSize = jsUtils.GetWindowSize();

	var div = document.body.appendChild(document.createElement("DIV"));
	div.id = "socnet_wait_window_div";
	div.className = "socnet-waitwindow";
	div.style.right = (5 - obWndSize.scrollLeft) + 'px';
	div.style.top = obWndSize.scrollTop + 5 + "px";

	if(jsUtils.IsIE())
	{
		var frame = document.createElement("IFRAME");
		frame.src = "javascript:''";
		frame.id = "socnet_wait_window_frame";
		frame.className = "socnet-waitwindow";
		frame.style.width = div.offsetWidth + "px";
		frame.style.height = div.offsetHeight + "px";
		frame.style.right = div.style.right;
		frame.style.top = div.style.top;
		document.body.appendChild(frame);
	}
	jsUtils.addEvent(document, "keypress", SocNetWaitOnKeyPress);
}

function SocNetCloseWaitWindow()
{
	jsUtils.removeEvent(document, "keypress", SocNetWaitOnKeyPress);

	var frame = document.getElementById("socnet_wait_window_frame");
	if(frame)
		frame.parentNode.removeChild(frame);

	var div = document.getElementById("socnet_wait_window_div");
	if(div)
		div.parentNode.removeChild(div);
}

/**********************************************************/

function sonet_check_group(el)
{
	var obj_div = document.getElementById(el.id+'_block');
	if(obj_div)
	{
		/*users in this group*/
		var obj = jsUtils.FindChildObject(obj_div, 'div', 'socnet-user-contact', true);
		do
		{
			var chbox = jsUtils.FindChildObject(obj, 'input', false, true);
			if(chbox)
				chbox.checked = el.checked;
		}
		while(obj = jsUtils.FindNextSibling(obj, 'div'));

		//subgroups
		obj = jsUtils.FindChildObject(obj_div, 'div', 'socnet-user-section', true);
		if(obj)
		{
			do
			{
				var chbox = jsUtils.FindChildObject(obj, 'input', false, true);
				if(chbox)
				{
					chbox.checked = el.checked;
					sonet_check_group(chbox);
				}
			}
			while(obj = jsUtils.FindNextSibling(obj, 'div'));
		}
	}
}

function sonet_switch_section(el, div_id)
{
	var bCollapse = (el.className == 'socnet-arrow socnet-arrow-down');
	el.className = (bCollapse? 'socnet-arrow socnet-arrow-right':'socnet-arrow socnet-arrow-down')
	document.getElementById(div_id).style.display = (bCollapse? 'none':'block');
}

function sonet_set_selector(el)
{
	var div = document.getElementById(el.id+'_div');
	if(div.style.display == 'block')
		return
	
	var parentDiv = jsUtils.FindParentObject(el, 'div');
	var selectorDiv = jsUtils.FindChildObject(parentDiv, 'div');
	do
	{
		div = document.getElementById(selectorDiv.id+'_div');
		if(selectorDiv == el)
		{
			selectorDiv.className = 'socnet-selector socnet-selector-active';
			div.style.display = 'block';
		}
		else
		{
			selectorDiv.className = 'socnet-selector';
			div.style.display = 'none';
		}
	}
	while((selectorDiv = jsUtils.FindNextSibling(selectorDiv, 'div')));
}

var sonetCachedUsers = false;
function sonet_cache_users()
{
	var form = document.getElementById('post_message_id').form;
	if(form['USER_ID[]'])
	{
		var aUserElements = (form['USER_ID[]'].length > 1? form['USER_ID[]'] : [form['USER_ID[]']]);
		for(var i=0; i<aUserElements.length; i++)
		{
			var td = jsUtils.FindParentObject(aUserElements[i], 'td');
			td = jsUtils.FindNextSibling(td, 'td');
			var el;
			if(!(el = jsUtils.FindChildObject(td, 'a')))
				el = jsUtils.FindChildObject(td, 'div');
			if(el)
			{
				if(!sonetCachedUsers)
					sonetCachedUsers = {};
				var id = aUserElements[i].value;
				if(!sonetCachedUsers[id])
					sonetCachedUsers[id] = [];
				sonetCachedUsers[id][sonetCachedUsers[id].length] = el;
			}
		}
	}
}

function sonet_set_online_users(aOnline)
{
	bMessOut = false;

	//when talking to specific user
	sonetChatUserId = parseInt(sonetChatUserId);
	if(sonetChatUserId > 0)
	{
		var bOnline = jsUtils.in_array(sonetChatUserId, aOnline);
		var onlineDiv = document.getElementById('socnet_user_online');
		if(onlineDiv)
		{
			var styleVal = (bOnline? 'bx-icon bx-icon-online':'bx-icon bx-icon-offline');
			if(onlineDiv.className != styleVal)
			{
				onlineDiv.className = styleVal;
				onlineDiv.title = (bOnline? messUserOnline : messUserOffline);
				document.getElementById('socnet_user_online_text').innerHTML = onlineDiv.title;
				sonetChatMsgs += '<br /><b>' + messageTrSystem + '</b><br />' + (bOnline? messageTrTalkOnline : messageTrTalkOutline) + '<br />';
				bMessOut = true;
			}
		}
	}

	//users in contact list
	if(!sonetCachedUsers)
		sonet_cache_users();

	if(sonetCachedUsers)
	{
		for(var i in sonetCachedUsers)
		{
			var bOnline = jsUtils.in_array(i, aOnline);
			for(var j=0; j<sonetCachedUsers[i].length; j++)
			{
				var el = sonetCachedUsers[i][j];
				if(el.className.indexOf('socnet-message') == -1)
				{
					var classname = (bOnline? 'socnet-status socnet-online':'socnet-status socnet-offline');
					if(el.className != classname)
						el.className = classname;
				}
			}
		}
	}

	return bMessOut;
}

function sonet_message_notify(user_id, message_id)
{
	if(!sonetCachedUsers)
		sonet_cache_users();

	if(sonetCachedUsers && sonetCachedUsers[user_id])
	{
		for(var j=0; j<sonetCachedUsers[user_id].length; j++)
		{
			var el = sonetCachedUsers[user_id][j];
			if(el.className.indexOf('socnet-message') == -1)
			{
				el.className += ' socnet-message';
				
				//find and replace message link
				var td = jsUtils.FindParentObject(el, 'td');
				td = jsUtils.FindNextSibling(td, 'td');
				var a = jsUtils.FindChildObject(td, 'a');
				if(a)
				{
					a.__bxSaveHref = a.href;
					a.__bxUserId = user_id;
					a.href = sonetReplyPathTemplate.replace(/#user_id#/i, user_id).replace(/#message_id#/i, message_id);
				}

			}
		}
	}
}

function sonet_group_resize(el)
{
	var cell = document.getElementById('socnet_user_group_cell');
	var bVisible = (cell.style.display != 'none');
	if(bVisible)
	{
		cell.style.display = 'none';
		el.className = 'socnet-divider';
	}
	else
	{
		cell.style.display = '';
		el.className = 'socnet-divider socnet-divider-right';
	}

	sonet_chat_onresize();
	sonet_save_option('socnet', 'chat', 'contacts', (bVisible? 'N':'Y'));
}

var sonet_tdToResize, sonet_divToResize, sonet_subDivToResize, sonet_curPos;
function sonet_start_drag(e)
{
	sonet_tdToResize = document.getElementById('socnet_user_group_cell');
	if(sonet_tdToResize.style.display == 'none')
		return;
	sonet_divToResize = document.getElementById('socnet_user_group');
	sonet_subDivToResize = document.getElementById('socnet_user_list');

	if(!e)
		e = window.event
	sonet_curPos = e.clientX;

	jsUtils.addEvent(document, "mousemove", sonet_resize_contact);
	document.onmouseup = sonet_stop_drag;

	var b = document.body;
    b.ondrag = jsUtils.False;
    b.onselectstart = jsUtils.False;
    b.style.MozUserSelect = 'none';
    b.style.cursor = 'e-resize';
}

function sonet_resize_contact(e)
{
	var x = e.clientX;
	if(sonet_curPos == x)
		return;

	var newWidth = sonet_divToResize.offsetWidth+(sonet_curPos - x);
	if(newWidth < 180)
	{
		sonet_divToResize.style.width = '180px';
		sonet_tdToResize.style.width = '180px';
		sonet_subDivToResize.style.width = '178px';
	}
	else
	{
		sonet_divToResize.style.width = newWidth+'px';
		sonet_tdToResize.style.width = newWidth+'px';
		sonet_subDivToResize.style.width = (newWidth-2)+'px';
	}

	sonet_curPos = x;
}

function sonet_stop_drag(e)
{
	jsUtils.removeEvent(document, "mousemove", sonet_resize_contact);
	document.onmouseup = null;

	var b = document.body;
	b.ondrag = null;
	b.onselectstart = null;
	b.style.MozUserSelect = '';
    b.style.cursor = '';

	sonet_chat_onresize();

	sonet_save_option('socnet', 'chat', 'contacts_width', parseInt(sonet_divToResize.style.width));
}

function sonet_open_chat(a)
{
	window.open(a.href, '', 
		'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+
		Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); 

	//restore icons and links from flashing messages icons
	for(var i in sonetCachedUsers)
	{
		for(var j=0; j<sonetCachedUsers[i].length; j++)
		{
			var el = sonetCachedUsers[i][j];
			if(a.__bxUserId == i && el.className.indexOf('socnet-message') != -1)
			{
				el.className = el.className.replace(/\s*socnet-message/i, '');
				//find and replace message link
				var td = jsUtils.FindParentObject(el, 'td');
				td = jsUtils.FindNextSibling(td, 'td');
				var anchor = jsUtils.FindChildObject(td, 'a');
				if(anchor)
					anchor.href = anchor.__bxSaveHref;
			}
		}
	}
}

function sonet_search_focus(el)
{
	if(el.value == messSeachSuggest)
		el.value = '';
	el.className = 'active';
}

function sonet_search_blur(el)
{
	el.className = '';
}

var sonetCurSearchEl = null;
function sonet_search_keypress(el)
{
	if(el.value == '')
	{
		if(sonetCurSearchEl)
			sonetCurSearchEl.innerHTML = (sonetCurSearchEl.innerText? sonetCurSearchEl.innerText : sonetCurSearchEl.textContent);
		return;
	}

	if(!sonetCachedUsers)
		sonet_cache_users();

	if(sonetCachedUsers)
	{
		var value = el.value.toLowerCase();
		var listDiv = document.getElementById('socnet_user_list');
		var listPos = jsUtils.GetRealPos(listDiv);

		for(var i in sonetCachedUsers)
		{
			var bFound = false;
			for(var j=0; j<sonetCachedUsers[i].length; j++)
			{
				var el = sonetCachedUsers[i][j];
				var td = jsUtils.FindNextSibling(jsUtils.FindParentObject(el, 'td'), 'td');
				var a = jsUtils.FindChildObject(td, 'a');
				var pos;
				var txt = (a.innerText? a.innerText : a.textContent);
				if((pos = txt.toLowerCase().indexOf(value)) != -1)
				{
					//open section if closed
					var section = el, first_section = null;
					while(section = jsUtils.FindParentObject(section, 'div', 'socnet-user-contact-block'))
					{
						if(section.style.display == 'none')
						{
							var sect_div = jsUtils.FindPreviousSibling(section, 'div', 'socnet-user-section');
							var arrow = jsUtils.FindChildObject(sect_div, 'div', 'socnet-arrow socnet-arrow-right', true);
							sonet_switch_section(arrow, section.id);
						}
						first_section = section;
					}
					
					//open selector if closed
					var selector = jsUtils.FindParentObject(first_section, 'div');
					if(selector.style.display == 'none')
					{
						var sel_id = selector.id.substring(0, selector.id.length-4); //socnet_selector_friends_div
						sonet_set_selector(document.getElementById(sel_id));
					}
					
					//highlight user name
					if(sonetCurSearchEl && a != sonetCurSearchEl)
						sonetCurSearchEl.innerHTML = (sonetCurSearchEl.innerText? sonetCurSearchEl.innerText : sonetCurSearchEl.textContent);
					a.innerHTML = txt.substring(0, pos)+'<span class="socnet-search-highlight">'+txt.substring(pos, pos+value.length)+'</span>'+txt.substring(pos+value.length);
					
					//scroll to selection
					var pos = jsUtils.GetRealPos(a);
					listDiv.scrollTop = pos['top']-listPos['top'];
					
					sonetCurSearchEl = a;
					bFound = true;
					break;
				}
			}
			if(bFound)
				break;
		}
	}
}

/**********************************************************/

function CreatePalette()
{
	if (oForumForm['PALETTE'])
		return oForumForm['PALETTE'];
	var color_range = ["00","33","66","99","BB","FF"];
	var rgb = {'R' : 0, 'G' : 0, 'B' : 0, 'color' : ''};
	var oDiv = document.body.appendChild(document.createElement("DIV"));
	oDiv.id = 'palette';
	oDiv.className = 'palette';
	oDiv.style.position = 'absolute';
	oDiv.style.width = '199px';
	oDiv.style.height = '133px';
	var text = '<table class="palette"><tr>';
	for (var ii = 0; ii < 216; ii++)
	{
		rgb['R'] = ii%6; rgb['G'] = Math.round(ii/36)%6; rgb['B'] = Math.round(ii/6)%6;
		rgb['color'] = '#' + color_range[rgb['R']] + '' + color_range[rgb['G']] + color_range[rgb['B']];
		if (ii%18 == 0 && ii > 0)
			text += '</tr><tr>';
		text += ('<td style="background-color:' + rgb['color'] + ';" '+
			'onmouseup="window.color_palette=\'#' + color_range[rgb['R']] + '' + color_range[rgb['G']] + color_range[rgb['B']] + '\'">'+
			'<div></div></td>');
	}
	text += "</tr></table>";
	oDiv.innerHTML = text;
	oForumForm['PALETTE'] = oDiv;
	return oForumForm['PALETTE'];
}

/* Form functions */
function init_form(form)
{
	if (typeof(form) != "object")
		return false;
	if (!oForumForm[form.id] || typeof(oForumForm[form.id]) != "object")
	{
		oForumForm[form.id] = new PostForm(form);
		oForumForm[form.id].Init(form);
	}
	return;
}

function PostForm()
{
	this.open = {"B" : 0, "I" : 0, "U" : 0, "QUOTE" : 0, "FONT" : 0, "COLOR": 0};
	this.tags =  {
		"B" : "simple_tag", "I" : "simple_tag", "U" : "simple_tag", 
		"QUOTE" : "simple_tag", "FONT" : "simple_tag", "PALETTE" : "show_palette", "COLOR" : "simple_tag", 
		"URL" : "tag_url", "IMG" : "tag_image"};
	this.stack = [];
	this.form = false;
	this.tools = {};
	this.nav = 'none';
	var t = this;
	this.popupMenu = false;
	this.now = {};
	
	this.Init = function(form)
	{
		if (this.form)
			return true;
		if (typeof(form) != "object")
			return false;
		this.form = form;
		/* Simple tags */ 
		oDivs = this.form.getElementsByTagName('DIV');
		if (oDivs && oDivs.length > 0)
		{
			for (var ii = 0; ii < oDivs.length; ii++)
			{
				if (!(oDivs[ii] && oDivs[ii].id && oDivs[ii].id.substring(0, 5) == "form_"))
					continue;
				oDiv = oDivs[ii];
				id = oDiv.id.substring(5).toUpperCase();
				if (id == 'QUOTE')
					oDiv.onmousedown = function(){t.quote();};
				else
					oDiv.onmousedown = function(){
						var id = this.id.substring(5).toUpperCase();
						var objTextarea = t.form['POST_MESSAGE'];
						var selected = false;
						if ((jsUtils.IsIE() || jsUtils.IsOpera()) && (objTextarea.isTextEdit))
						{
							objTextarea.focus();
							var sel = document.selection;
							var rng = sel.createRange();
							rng.colapse;
							if (sel.type=="Text" && rng != null)
							{
								selected = true;
							}
						}
						else if (document.getElementById && (objTextarea.selectionEnd > objTextarea.selectionStart))
						{
							selected = true;
						}
						t.now[id] = false;
						if (!selected)
						{
							return true;
						}
						t.format_text(this, 'onmousedown');
						t.now[id] = true;};
				oDiv.onclick = function(){t.format_text(this, 'onclick')};
				oDiv.onmouseover = function(){this.className += ' marked';};
				oDiv.onmouseout = function(){this.className = this.className.replace(/marked/, '').replace('  ', ' ');};
				if (jsUtils.IsOpera() && oDiv.title)
					oDiv.title = oDiv.title.replace(/\(alt+([^)])+\)/gi, '');
				this.tools[id] = oDiv;
				
			}
		}
		if (this.form['FONT'])
		{
			this.form['FONT'].onchange = function(){t.format_text(this)};
		}
		
		var image = this.form.getElementsByTagName("img");
		if (image && image.length > 0)
		{
			for (var ii = 0; ii < image.length; ii++ )
			{
				if (image[ii].className == "chat-smile")
					image[ii].onclick = function(){t.emoticon(this)};
			}
		}
		
		if (this.form["POST_MESSAGE"])
		{
			this.form["POST_MESSAGE"].onkeyup = function(e){t.OnKeyPress(e);};
			this.form["POST_MESSAGE"].onkeypress = t.check_ctrl_enter;
		}
		
		return true;
	}, 
	
	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(!e.altKey) return;
		if(e.keyCode == 73)
			this.format_text({'id' : 'form_i', 'value' : ''});
		else if(e.keyCode == 85)
			this.format_text({'id' : 'form_u', 'value' : ''});
		else if(e.keyCode == 66)
			this.format_text({'id' : 'form_b', 'value' : ''});
		else if(e.keyCode == 81)
			this.format_text({'id' : 'form_quote', 'value' : ''});
		else if(e.keyCode == 71)
			this.tag_image();
		else if(e.keyCode == 72)
			this.tag_url();
	},
	
	this.Insert = function (ibTag, ibClsTag, isSingle, postText)
	{
		if (!this.form || !this.form["POST_MESSAGE"])
			return false;
		var textarea = this.form["POST_MESSAGE"];
		var isClose = (isSingle ? true : false);
		postText = (postText == null ? "" : postText);
		this.form["POST_MESSAGE"].focus();
		if (jsUtils.IsIE())
		{
			this.form["POST_MESSAGE"].focus();

			if (sonetChatSelection != false && sonetChatSelectionRange != false)
			{
				var sel = sonetChatSelection;
				var rng = sonetChatSelectionRange;
				sonetChatSelection = false;
				sonetChatSelectionRange = false;
			}
			else
			{
				var sel = document.selection;
				var rng = sel.createRange();
			}

			rng.collapse;
			var parent_name = "";
			if (rng.parentElement && rng.parentElement() && rng.parentElement().name)
			{
				parent_name = rng.parentElement().name;
			}
			if (parent_name != "POST_MESSAGE")
			{
				textarea.value += ibTag
			}
			else if ((sel.type == "Text" || sel.type == "None") && rng != null)
			{
				if (ibClsTag != "" && rng.text.length > 0)
				{
					ibTag += rng.text + ibClsTag;
					isClose = false;
				}
				else if (postText.length > 0)
				{
					ibTag += postText + ibClsTag;
					isClose = false;
				}
				rng.text = ibTag;
			}
		}
		else if (document.getElementById)
		{
			var text = {"start" : ibTag, "end" : ""};
			if (ibClsTag != "" && textarea.selectionEnd > textarea.selectionStart)
			{
				text["end"] = ibClsTag;
				isClose = false;
			}
			else if (postText.length > 0)
			{
				text["start"] = text["start"] + "" + postText + "" + ibClsTag;
				isClose = false;
			}
			text["start"] = (!text["start"] ? "" : text["start"]);
			text["end"] = (!text["end"] ? "" : text["end"]);
			var sel = {
				"start" : textarea.selectionStart,
				"end" : textarea.selectionEnd};
			
			if (sel["end"] == 1 || sel["end"] == 2)
				sel["end"] = textarea.textLength;
		
			var s1 = (textarea.value).substring(0, sel["start"]);
			var s2 = (textarea.value).substring(sel["start"], sel["end"])
			var s3 = (textarea.value).substring(sel["end"], textarea.textLength);
			textarea.value = s1 + text["start"] + s2 + text["end"] + s3;
			if (sel["start"] != sel["end"])
			{
				textarea.selectionStart = sel["start"];
				textarea.selectionEnd = sel["end"] + text["start"].length + text["end"].length;
			}
			else if (text["start"].length > 0 || text["end"].length > 0)
			{
				textarea.selectionStart = sel["end"] + text["start"].length + text["end"].length;
				textarea.selectionEnd = textarea.selectionStart;
			}
		} 
		else
		{
			textarea.value += ibTag;
		}
		textarea.focus();
		return isClose;
	},
	
	this.format_text = function(oObj, event_name)
	{
		if (!oObj || !oObj.id)
			return false;
		var id = oObj.id.substring(5).toUpperCase();
		if (this.now[id] == true)
		{
			this.now[id] = false;
			return;
		}
		
		if (this.tags[id] == 'simple_tag')
		{
			var tag_start = tag_name = id;
			if (tag_name == 'FONT' || tag_name == 'COLOR')
				tag_start += "=" + oObj.value;

			if (this.open[tag_name] == 0 && (tag_name != 'FONT' || oObj.value != '0') &&
				this.Insert("[" + tag_start + "]", "[/" + tag_name + "]", true))
			{
				this.open[tag_name]++;
				if (this.tools[id])
					this.tools[id].className += ' opened';
				this.stack.push(tag_name);
			}
			else
			{
				var stack_need_insert = [];
				var tag_is_open = false;
				var res = false;
				while (res = this.stack.pop())
				{
					stack_need_insert.unshift(res);
					if (res == tag_name)
					{
						tag_is_open = true;
						break;
					}
				}
				if (!tag_is_open)
					this.stack = stack_need_insert;
				var res = false;
				while (res = stack_need_insert.pop())
				{
					this.Insert("[/" + res + "]", "", false);
					if (this.tools[id])
						this.tools[id].className = this.tools[id].className.replace(/opened/, '').replace('  ', ' ');
					
					this.open[res]--;
				}
			}
		}
		else if (this.tags[id] == 'show_palette')
		{
			this.show_palette(oObj);
		}
		else if (this.tags[id])
		{
			this[this.tags[id]]();
		}
	}, 
	
	this.check_ctrl_enter = function(e)
	{
		if(!e) e = window.event;
		if(!e) return;
		if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey)
			t.form.onsubmit();
	},
	
	this.emoticon = function(element)
	{
		this.Insert(" " + element.id + " ", "", false);
	},
	
	this.tag_image = function()
	{
		var need_loop = true;
		do 
		{
			var res = prompt(oText['enter_image'], "http://");
			if (res == null)
			{
				need_loop = false;
				return false;
			}
			else if (res.length <= 0)
			{
				alert("Error! " + oErrors['no_url']);
			}
			else
			{
				need_loop = false;
			}
		}
		while(need_loop);
		this.Insert("[IMG]" + res + "[/IMG]", "", false);
	},
	
	this.tag_url = function( )
	{
		var FoundErrors = '';
		var need_loop = true;
		var oFields = {
			"URL" : {
				"text" : oText['enter_url'],
				"default" : "http://",
				"error" : oErrors['no_url'],
				"value" : ""}, 
			"TITLE" : {
				"text" : oText['enter_url_name'],
				"default" : "My Webpage",
				"error" : oErrors['no_title'],
				"value" : ""}};

		for (var ii in oFields)
		{
			need_loop = true;
			do 
			{
				var res = prompt(oFields[ii]["text"], oFields[ii]["default"]);
				if (res == null)
				{
					need_loop = false;
					return false;
				}
				else if (res.length <= 0)
				{
					alert("Error! " + oFields[ii]["error"]);
				}
				else
				{
					oFields[ii]["value"] = res;
					need_loop = false;
				}
			}
			while(need_loop);
		}
		
		this.Insert("[URL=" + oFields["URL"]["value"] + "]" + oFields["TITLE"]["value"] + "[/URL]", "", false);
	},
	
	this.quote = function ()
	{
		var selection = "";
		var message_id = 0;
		if (document.getSelection)
		{
			selection = document.getSelection();
			selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_").replace(/\r\n/gi, " ");
			selection = selection.replace(/  /gi, "").replace(/_newstringhere_/gi, "\r\n\r\n");
		}
		else if (document.selection)
		{
			selection = document.selection.createRange().text;
		}
		
		if (selection != "")
		{
			selection = selection.replace(/\&shy;/gi, "");
			this.Insert("[QUOTE]", "[/QUOTE]", false, selection);
			this.now['QUOTE']=true;
		}
	}, 
	
	this.show_palette = function(oObj)
	{
		if (!oObj){return false};
		
		if (jsUtils.IsIE())
		{
			sonetChatSelection = document.selection;
			sonetChatSelectionRange = sonetChatSelection.createRange();
		}

		var oPalette = CreatePalette();
		if (!this.popupMenu)
		{
			window.ChatPopupMenu.prototype.ShowMenu = function(control, div)
			{
				var pos = {"top" : 20, "left" : 20};
				this.PopupHide();
				if (typeof(control) == "object")
				{
					id = control.id;
					pos = jsUtils.GetRealPos(control);
					this.ControlPos = pos;
					this.oControl = control;
				}
				
				this.oDiv = div;
				if (this.oDiv)
				{
					this.PopupShow(pos, this.oDiv);
				}
			}
			window.ChatPopupMenu.prototype.CheckClick = function(e)
			{
				if(!this.oDiv){return;}
				if (this.oDiv.style.visibility != 'visible' || this.oDiv.style.display == 'none')
					return;
		        var windowSize = jsUtils.GetWindowSize();
		        var x = e.clientX + windowSize.scrollLeft;
		        var y = e.clientY + windowSize.scrollTop;
		
				/*menu region*/
				pos = jsUtils.GetRealPos(this.oDiv);
				var posLeft = parseInt(pos["left"]);
				var posTop = parseInt(pos["top"])
				var posRight = posLeft + this.oDiv.offsetWidth;
				var posBottom = posTop + this.oDiv.offsetHeight;
				if(x >= posLeft && x <= posRight && y >= posTop && y <= posBottom)
				{
					if (window.color_palette)
					{
						t.format_text({'id' : 'form_color', 'value' : window.color_palette, 'className' : ''});
						this.PopupHide();
					}
				}
		
				if(this.ControlPos)
				{
					var pos = this.ControlPos;
					if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
						return;
				}
				this.PopupHide();
			}
			
			this.popupMenu = new ChatPopupMenu();
		}
		this.popupMenu.ShowMenu(oObj, oPalette);
	}
}

//*********************************

function ChatPopupMenu()
{
	this.oDiv = false;
	this.oControl = false;
	this.oControlPos = false;
	this.bRemoveElement = true;
	var _this = this;
}

ChatPopupMenu.prototype.PopupShow = function(pos, div, controlpos)
{
	if (!this.oDiv && !div || (typeof(this.oDiv) != "object" && typeof(div) != "object"))
		return false;
	if (div)
	{
		if (controlpos)
			this.ControlPos = controlpos;
		this.bRemoveElement = false;
		this.oDiv = div;
	}
	else
	{
		this.bRemoveElement = true;
	}

	var w = this.oDiv.offsetWidth; var h = this.oDiv.offsetHeight;
	if (h > 250)
	{
		this.oDiv.style.height = "250px";
		this.oDiv.style.overflow = "auto";
		w += 15;
		this.oDiv.className = "forum-popup-oveflow";
		h = 250;
	}
	pos = jsUtils.AlignToPos(pos, w, h);
	for (var ii in pos)
	{
		if (isNaN(pos[ii]) || !pos[ii] || pos[ii] <= 0)
			pos[ii] = 0;
	}
	this.oDiv.style.width = w + 'px'; 
	this.oDiv.style.visibility = 'visible';
//	this.oDiv.style.display = 'block';
	this.oDiv.style.MozUserSelect = 'none';
	jsFloatDiv.Show(this.oDiv, parseInt(pos["left"]), parseInt(pos["top"]), false, false, false);
	if (this.oControl != null && this.oControl.className)
	{
		this.oControl.className += ' forum-popup-control-active';
	}
	var _this = this;
	jsUtils.addEvent(document, "click", function(e){_this.CheckClick(e)});
	jsUtils.addEvent(document, "keypress", function(e){_this.OnKeyPress(e)});
}

ChatPopupMenu.prototype.PopupHide = function()
{ 
	var _this = this;
	jsUtils.removeEvent(document, "click", function(e){_this.CheckClick(e);});
	jsUtils.removeEvent(document, "keypress", function(e){_this.OnKeyPress(e)});

	if (!this.oDiv)
		return false;
	jsFloatDiv.Close(this.oDiv);
	if (this.oControl != null && this.oControl.className)
	{
		this.oControl.className = this.oControl.className.replace(" forum-popup-control-active", "");
	}
	if (this.bRemoveElement)
	{
		try
		{
			this.oDiv.parentNode.removeChild(this.oDiv);
			this.ControlPos = false;
		}
		catch(e)
		{}
	}
	this.oDiv.style.visibility = 'hidden';
//	this.oDiv.style.display = 'none';
}

ChatPopupMenu.prototype.OnKeyPress = function(e)
{
	if(!e) e = window.event
	if(!e) return;
	if(e.keyCode == 27)
		this.PopupHide();
}
