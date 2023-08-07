var waitDiv = null;
var waitPopup = null;
var waitTimeout = null;
var waitTime = 500;

function __UFEtoggleCheckbox(ev, block, user_code)
{
	ev = ev || window.event;

	if (user_code == 'undefined' || !user_code)
		return false;

	var type = user_code.substr(0, 1);
	var user_id_tmp = parseInt(user_code.substr(1));
	
	switch (type) {
		case 'F':
			if (BX.util.in_array(user_id_tmp, actionUsers['Friends']))
				actionUsers['Friends'].splice(BX.util.array_search(user_id_tmp, actionUsers['Friends']), 1);
			else
				actionUsers['Friends'][actionUsers['Friends'].length] = user_id_tmp;		
			break;
		case 'B':
			if (BX.message("UFEUseBan") == "Y")
			{
				if (BX.util.in_array(user_id_tmp, actionUsers['Banned']))
					actionUsers['Banned'].splice(BX.util.array_search(user_id_tmp, actionUsers['Banned']), 1);
				else
					actionUsers['Banned'][actionUsers['Banned'].length] = user_id_tmp;		
			}
			break;
		default:
			return false;
	}

	var check_box = BX.findChild(block, { tagName: 'input' }, true, false);

	if(ev.target == check_box || ev.srcElement == check_box){
		BX.toggleClass(block.parentNode, 'sonet-members-member-block-active');
		return false;
	}
	else{
		BX.toggleClass(block.parentNode, 'sonet-members-member-block-active');
		check_box.checked = check_box.checked == true ? false : true;
	}

	BX.PreventDefault(ev);
}

function __UFEShowMenu(bindElement, type)
{
	if (!type)
		type = 'friends';

	var arItems = [];

	if (type == 'friends')
	{
		if (BX.message("UFEIsCurrentUser"))
		{
			arItems[arItems.length] = { text : BX.message('UFEAddToFriendsTitle'), className : "menu-popup-no-icon", href: BX.message('UFEPathToUserSearch') };
			arItems[arItems.length] = { text : BX.message('UFEExcludeFromFriendsTitle'), className : "menu-popup-no-icon", onclick : function(e) { __UFEExcludeFromFriends(this.popupWindow); return BX.PreventDefault(e); } };

			if (BX.message("UFEUseBan") == "Y")
				arItems[arItems.length] = { text : BX.message('UFEBanTitle'), className : "menu-popup-no-icon", onclick : function(e) { __UFEBan(this.popupWindow); return BX.PreventDefault(e); } };
		}
	}
	else if (type == 'ban')
	{
		if (BX.message("UFEIsCurrentUser"))
			arItems[arItems.length] = { text : BX.message('UFEUnBanTitle'), className : "menu-popup-no-icon", onclick : function(e) { __UFEUnBan(this.popupWindow); return BX.PreventDefault(e); } };
	}

	if (arItems.length > 0)
	{
		var arParams = {
			offsetLeft: -32,
			offsetTop: 4,
			lightShadow: false,
			angle: {position: 'top', offset : 60}
		};

		BX.PopupMenu.show("ufe-menu-" + type, bindElement, arItems, arParams);
	}

}

function __UFEExcludeFromFriends(popup)
{
	if(confirm(BX.message('UFEExcludeFromFriendsConfirmTitle'))) 
	{
		if (actionUsers['Friends'].length > 0)
		{
			__UFEShowWait();
			BX.ajax({
				url: '/bitrix/components/bitrix/socialnetwork.user_friends.ex/ajax.php',
				method: 'POST',
				dataType: 'json',
				data: {'ACTION': 'EX', 'USER_ID': parseInt(BX.message('UFEUserId')), 'FRIEND_ID' : actionUsers['Friends'], 'sessid': BX.bitrix_sessid(), 'site': BX.util.urlencode(BX.message('UFESiteId'))},
				onsuccess: function(data) { __UFEProcessAJAXResponse(data, popup); }
			});
		}
		else
			__UFEShowError(BX.message('UFEErrorFriendIDNotDefined'));
	} 
}

function __UFEBan(popup)
{
	if (actionUsers['Friends'].length > 0)
	{
		__UFEShowWait();
		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.user_friends.ex/ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {'ACTION': 'BAN', 'USER_ID': parseInt(BX.message('UFEUserId')), 'FRIEND_ID' : actionUsers['Friends'], 'sessid': BX.bitrix_sessid(), 'site': BX.util.urlencode(BX.message('UFESiteId'))},
			onsuccess: function(data) { __UFEProcessAJAXResponse(data, popup); }
		});
	}
	else
		__UFEShowError(BX.message('UFEErrorFriendIDNotDefined'));
}

function __UFEUnBan(popup)
{
	if (actionUsers['Banned'].length > 0)
	{
		__UFEShowWait();
		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.user_friends.ex/ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {'ACTION': 'UNBAN', 'USER_ID': parseInt(BX.message('UFEUserId')), 'FRIEND_ID' : actionUsers['Banned'], 'sessid': BX.bitrix_sessid(), 'site': BX.util.urlencode(BX.message('UFESiteId'))},
			onsuccess: function(data) { __UFEProcessAJAXResponse(data, popup); }
		});
	}
	else
		__UFEShowError(BX.message('UFEErrorFriendIDNotDefined'));
}

function __UFEProcessAJAXResponse(data, popup)
{
	if (popup == 'undefined' || popup == null || !popup.isShown())
		return false;

	if (data["SUCCESS"] != "undefined" && data["SUCCESS"] == "Y")
	{
		popup.close();
		BX.reload();
	}
	else if (data["ERROR"] != "undefined" && data["ERROR"].length > 0)
	{
		if (data["ERROR"].indexOf("USER_ACTION_FAILED", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorActionFailedPattern').replace("#ERROR#", data["ERROR"].substr(20)));
			return false;
		}
		else if (data["ERROR"].indexOf("SESSION_ERROR", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorSessionWrong'));
			BX.reload();
		}
		else if (data["ERROR"].indexOf("USER_FRIEND_NO_PERMS", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorNoPerms'));
			return false;
		}
		else if (data["ERROR"].indexOf("FRIEND_ID_NOT_DEFINED", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorFriendIDNotDefined'));
			return false;
		}
		else if (data["ERROR"].indexOf("FRIEND_ID_INCORRECT_2", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorFriendIDIncorrect2'));
			return false;
		}
		else if (data["ERROR"].indexOf("USER_ID_NOT_DEFINED", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorUserIdNotDefined'));
			return false;
		}
		else if (data["ERROR"].indexOf("CURRENT_USER_NOT_AUTH", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorCurrentUserNotAuthorized'));
			return false;
		}
		else if (data["ERROR"].indexOf("SONET_MODULE_NOT_INSTALLED", 0) === 0)
		{
			__UFEShowError(BX.message('UFEErrorModuleNotInstalled'));
			return false;
		}
		else
		{
			__UFEShowError(data["ERROR"]);
			return false;		
		}
	}
}
				
function __UFEShowError(errorText) 
{
	__UFECloseWait();

	var errorPopup = new BX.PopupWindow('ufe-error' + Math.random(), window, {
		autoHide: true,
		lightShadow: false,
		zIndex: 2,
		content: BX.create('DIV', {props: {'className': 'sonet-members-error-text-block'}, html: errorText}),
		closeByEsc: true,
		closeIcon: true
	});
	errorPopup.show();

}

function __UFEShowWait(timeout)
{
	if (timeout !== 0)
	{
		return (waitTimeout = setTimeout(function(){
			__UFEShowWait(0)
		}, 50));
	}

	if (!waitPopup)
	{
		waitPopup = new BX.PopupWindow('ufe_wait', window, {
			autoHide: true,
			lightShadow: true,
			zIndex: 2,
			content: BX.create('DIV', {
				props: {
					className: 'sonet-members-wait-cont'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: 'sonet-members-wait-icon'
						}
					}),
					BX.create('DIV', {
						props: {
							className: 'sonet-members-wait-text'
						},
						html: BX.message('UFEWaitTitle')
					})
				]
			})
		});
	}
	else
		waitPopup.setBindElement(window);

	waitPopup.show();
}

function __UFECloseWait()
{
	if (waitTimeout)
	{
		clearTimeout(waitTimeout);
		waitTimeout = null;
	}

	if (waitPopup)
		waitPopup.close();
}
