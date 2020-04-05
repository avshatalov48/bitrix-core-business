(function(){

if (!!BX.BXSURG)
{
	return;
}

BX.BXSURG = {
	iframe: false,
	errorBlock: null
};

BX.BXSURG.init = function(params) {

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
	}


	BX.bind(BX("sonet_group_user_request_button_submit"), "click", BX.delegate(function(event) {
		BX.BXSURG.submitForm();
		event.preventDefault();
	}, this));

	BX.bind(BX("sonet_group_user_request_button_cancel"), "click", function(event) {
		BX.SidePanel.Instance.close();
		event.preventDefault();
	});
};

BX.BXSURG.submitForm = function() {
	if (!BX('sonet_group_user_request_form'))
	{
		return;
	}

	BX.SocialnetworkUICommon.hideError(this.errorBlock);
	BX.SocialnetworkUICommon.showButtonWait(BX('sonet_group_user_request_button_submit'));

	BX.ajax({
		url: BX('sonet_group_user_request_form').getAttribute('action'),
		method: 'POST',
		dataType: 'json',
		data: {
			ajax_request: 'Y',
			MESSAGE: BX('sonet_group_user_request_message').value,
			save: 'Y',
			sessid: BX.bitrix_sessid()
		},
		onsuccess: BX.delegate(function(responseData) {
			BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_user_request_button_submit'));

			if (
				typeof responseData.MESSAGE != 'undefined'
				&& responseData.MESSAGE == 'SUCCESS'
				&& typeof responseData.URL != 'undefined'
			)
			{
				if (window !== top.window) // frame
				{
					window.top.BX.SidePanel.Instance.close();
					window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
						code: 'afterJoinRequestSend',
						data: {
							groupId: this.groupId
						}
					});
				}
				else
				{
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
			BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_user_request_button_submit'));
			BX.SocialnetworkUICommon.showError(BX.message('SONET_C39_T_ERROR'), this.errorBlock);
		}, this)
	});
};
})();