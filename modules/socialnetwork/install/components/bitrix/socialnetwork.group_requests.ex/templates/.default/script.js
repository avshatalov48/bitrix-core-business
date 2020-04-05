(function() {

if (!!BX.BXSGRE) {
	return;
}

BX.BXSGRE = {
	iframe: false,
	errorBlock: null,
	mode: null
};

BX.BXSGRE.init = function(params) {
	if (typeof (params) != 'undefined')
	{
		if (typeof (params.iframe) != 'undefined')
		{
			this.iframe = !!params.iframe;
		}

		if (
			BX.type.isNotEmptyString(params.errorBlockName)
			&& BX(params.errorBlockName)
		)
		{
			this.errorBlock = BX(params.errorBlockName);
		}

		if (typeof (params.mode) != 'undefined')
		{
			this.mode = params.mode;
		}
	}

	if (BX('sonet_group_requests_in_form_button_submit'))
	{
		BX.bind(BX("sonet_group_requests_in_form_button_submit"), "click", BX.delegate(function(event) {
			this.submitForm(event, {
				type: 'in',
				action: 'accept'
			});
			event.preventDefault();
		}, this));
	}

	if (BX('sonet_group_requests_in_form_button_reject'))
	{
		BX.bind(BX("sonet_group_requests_in_form_button_reject"), "click", BX.delegate(function(event) {
			this.submitForm(event, {
				type: 'in',
				action: 'reject'
			});
			event.preventDefault();
		}, this));
	}

	if (BX('sonet_group_requests_out_form_button_reject'))
	{
		BX.bind(BX("sonet_group_requests_out_form_button_reject"), "click", BX.delegate(function(event) {
			this.submitForm(event, {
				type: 'out',
				action: 'reject'
			});
			event.preventDefault();
		}, this));
	}

	if (BX('sonet_group_requests_in_check_all'))
	{
		BX.bind(BX("sonet_group_requests_in_check_all"), "click", BX.delegate(function(event) {
			this.checkAll(event.currentTarget);
		}, this));
	}

	if (BX('sonet_group_requests_out_check_all'))
	{
		BX.bind(BX("sonet_group_requests_out_check_all"), "click", BX.delegate(function(event) {
			this.checkAll(event.currentTarget);
		}, this));
	}

	this.processUserList(BX('invite-main-wrap-in'));
	this.processUserList(BX('invite-main-wrap-out'));

	BX.addCustomEvent('SidePanel.Slider:onMessage', BX.delegate(function(event){
		if (event.getEventId() == 'sonetGroupEvent')
		{
			var eventData = event.getData();
			if (
				BX.util.in_array(this.mode, ['ALL', 'OUT'])
				&& BX.type.isNotEmptyString(eventData.code)
				&& eventData.code == 'afterInvite'
			)
			{
				BX.SocialnetworkUICommon.reload();
			}
		}
	}, this));
};

BX.BXSGRE.processUserList = function(listNode)
{
	if (BX(listNode))
	{
		var userId = null;
		if (BX(listNode))
		{
			var userNodeList = BX.findChildren(BX(listNode), { className: 'invite-user-link'}, true);
			for (var i = 0, length = userNodeList.length; i < length; i++)
			{
				BX.bind(userNodeList[i], 'click', function(e) {
					if (BX.type.isNotEmptyString(BX.getEventTarget(e).href))
					{
						top.location.href = BX.getEventTarget(e).href;
					}
					e.preventDefault();
				});

				userId = userNodeList[i].getAttribute('bx-user-id');
				if (userId)
				{
					BX.tooltip(userId, userNodeList[i].id);
				}
			}
		}
	}
};

BX.BXSGRE.submitForm = function(event, params) {

	var
		type = null,
		action = null;

	if (typeof (params) != 'undefined')
	{
		if (BX.type.isNotEmptyString(params.type))
		{
			type = params.type;
		}

		if (BX.type.isNotEmptyString(params.action))
		{
			action = params.action;
		}
	}

	if (
		!type
		|| !action
	)
	{
		return false;
	}

	var button = event.currentTarget;

	BX.SocialnetworkUICommon.hideError(this.errorBlock);
	BX.SocialnetworkUICommon.showButtonWait(button);

	var form_name = (type == "out") ? "form_requests_out" : "form_requests";

	if (BX("requests_action_" + type))
	{
		BX("requests_action_" + type).value = action;
	}

	BX.ajax.submitAjax(
		document.forms[form_name],
		{
			url: document.forms[form_name].getAttribute('action'),
			method: 'POST',
			dataType: 'json',
			onsuccess: BX.delegate(function(responseData) {
				BX.SocialnetworkUICommon.hideButtonWait(button);

				if (
					typeof responseData.MESSAGE != 'undefined'
					&& responseData.MESSAGE == 'SUCCESS'
					&& typeof responseData.URL != 'undefined'
				)
				{
					if (window === top.window) // not frame
					{
						top.location.href = responseData.URL;
					}
					else // frame
					{
						window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
							code: (type == 'out' ? 'afterRequestOutDelete' : 'afterRequestDelete'),
							data: {}
						});
						BX.SocialnetworkUICommon.reload();
					}
				}
				else if (
					typeof responseData.MESSAGE != 'undefined'
					&& responseData.MESSAGE == 'ERROR'
					&& typeof responseData.ERROR_MESSAGE != 'undefined'
					&& responseData.ERROR_MESSAGE.length > 0
				)
				{
					BX.SocialnetworkUICommon.showError(responseData.ERROR_MESSAGE, this.errorBlock);
				}
			}, this),
			onfailure: BX.delegate(function(responseData) {
				BX.SocialnetworkUICommon.hideButtonWait(button);
				BX.SocialnetworkUICommon.showError(BX.message('SONET_GRE_T_ERROR'), this.errorBlock);
			}, this)
		}
	);

	return false;
};

BX.BXSGRE.checkAll = function(input) {
	var
		i = null,
		input_list = BX.findChildren(input.parentNode.parentNode.parentNode, { tag: 'input' }, true);
	if (!input.checked)
	{
		for(i=1; i<input_list.length; i++)
		{
			input_list[i].checked = false;
			BX.removeClass(input_list[i].parentNode.parentNode.parentNode, 'invite-list-active')
		}
	}
	else
	{
		for(i=1; i<input_list.length; i++)
		{
			input_list[i].checked = true;
			BX.addClass(input_list[i].parentNode.parentNode.parentNode, 'invite-list-active')
		}
	}
};

})();