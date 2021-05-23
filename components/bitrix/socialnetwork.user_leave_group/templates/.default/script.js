(function(){

if (!!BX.BXSULG)
{
	return;
}

BX.BXSULG = {
	groupId: null,
	errorBlock: null
};

BX.BXSULG.init = function(params) {

	if (typeof (params) != 'undefined')
	{
		if (
			BX.type.isNotEmptyString(params.errorBlockName)
			&& BX(params.errorBlockName)
		)
		{
			this.errorBlock = BX(params.errorBlockName);
		}

		if (
			typeof params.groupId != 'undefined'
			&& parseInt(params.groupId) > 0
		)
		{
			this.groupId = parseInt(params.groupId);
		}
	}

	BX.bind(BX("sonet_group_user_leave_button_submit"), "click", BX.delegate(function(event) {
		BX.BXSULG.submitForm();
		event.preventDefault();
	}, this));

	BX.bind(BX("sonet_group_user_leave_button_cancel"), "click", function(event) {
		BX.SidePanel.Instance.close();
		event.preventDefault();
	});
};

BX.BXSULG.submitForm = function() {
	if (!BX('sonet_group_user_leave_form'))
	{
		return;
	}

	BX.SocialnetworkUICommon.hideError(this.errorBlock);
	BX.SocialnetworkUICommon.showButtonWait(BX('sonet_group_user_leave_button_submit'));

	BX.ajax({
		url: BX('sonet_group_user_leave_form').getAttribute('action'),
		method: 'POST',
		dataType: 'json',
		data: {
			ajax_request: 'Y',
			save: 'Y',
			sessid: BX.bitrix_sessid()
		},
		onsuccess: BX.delegate(function(responseData) {
			BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_user_leave_button_submit'));

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
						code: 'afterLeave',
						data: {
							groupId: this.groupId
						}
					});
				}
				else
				{
					top.location.href = responseData.URL;
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
			BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_user_leave_button_submit'));
			BX.SocialnetworkUICommon.showError(BX.message('SONET_EXT_COMMON_AJAX_ERROR'), this.errorBlock);
		}, this)
	});
};

})();