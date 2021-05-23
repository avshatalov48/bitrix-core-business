function __URESubmitForm(type, action) 
{
	var form_name = (type == "out") ? "form_requests_out" : "form_requests";
	if (BX("requests_action_" + type))
		BX("requests_action_" + type).value = action;
	BX.submit(BX(form_name));
	return false;
}

function __URECheckedAll(input) 
{
	var input_list = BX.findChildren(input.parentNode.parentNode.parentNode, { tag: 'input' }, true);
	if (!input.checked)
	{
		for(var i=1; i<input_list.length; i++)
		{
			input_list[i].checked = false;
			BX.removeClass(input_list[i].parentNode.parentNode.parentNode, 'invite-list-active')
		}
	}
	else
	{
		for(var i=1; i<input_list.length; i++)
		{
			input_list[i].checked = true;
			BX.addClass(input_list[i].parentNode.parentNode.parentNode, 'invite-list-active')
		}
	}
}

function __UREIsLeftClick(event)
{
	if (!event.which && event.button !== undefined)
	{
		if (event.button & 1)
			event.which = 1;
		else if (event.button & 4)
			event.which = 2;
		else if (event.button & 2)
			event.which = 3;
		else
			event.which = 0;
	}

	return event.which == 1 || (event.which == 0 && BX.browser.IsIE());
};

function __hideInvitationItem(params)
{
	var invite_code = params.NOTIFY_TAG.split('|')[1],
		invite_id = params.NOTIFY_TAG.split('|')[3],
		invitation_item = BX(invite_code + '_' + invite_id);

	if (invitation_item)
	{
		BX.remove(invitation_item);
		
		//if no other invitations exist, hide buttons
		var requests_list = BX.findChildren(BX('form_requests'), { tag: 'td', className: 'invite-list-checkbox' }, true);
		var invite_list_nav = BX.findChild(BX('form_requests'), { tag: 'div', className: 'navigation'}, true);
		if (!requests_list && !invite_list_nav)
		{
			var invitation_table = BX.findChild(BX('form_requests'), { tag: 'table', className: 'invite-list' }, true);
			BX.addClass(invitation_table, 'invite-list-hidden');

			var buttons_area = BX.findChild(BX('form_requests'), { tag: 'span', className: 'invite-buttons-area' }, true);
			BX.addClass(buttons_area, 'invite-buttons-area-hidden');

			var group_info = BX.findChild(BX('form_requests'), { tag: 'span', className: 'sonet-group-requests-info' }, true);
			BX.removeClass(group_info, 'sonet-group-requests-info-hidden');
		}
	}
}