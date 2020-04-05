(function(){

if (!!BX.BXSF)
{
	return;
}

BX.BXSF = {
	iframe: false,
	errorBlock: null
};

BX.BXSF.init = function(params) {
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

	BX.bind(BX("sonet_group_features_form_button_cancel"), "click", function(event) {
		BX.SidePanel.Instance.close();
		event.preventDefault();
	});

	var items = BX.findChildren(BX('sonet-features-form'), {className:'sn-features-row'}, true);
	if (
		items
		&& BX("sonet_group_features_form_button_submit")
	)
	{
		BX.bind(BX("sonet_group_features_form_button_submit"), "click", BX.delegate(function(event) {
			BX.BXSF.submitForm();
			event.preventDefault();
		}, this));
	}

	var feature = null;
	items = BX.findChildren(BX('sonet-features-form'), {className:'settings-right-enable-checkbox'}, true);
	for(var i=0; i < items.length; i++)
	{
		BX.bind(items[i], "click", BX.delegate(function(e) {
			var node = e.currentTarget;
			feature = node.getAttribute('bx-feature');
			if (BX.type.isNotEmptyString(feature))
			{
				BX.BXSF.toggleInternalBlock(node.checked, feature);
			}
		}, this));
	}
};

BX.BXSF.toggleInternalBlock = function(chk, type) {
	var el = BX(type + "_body");
	if (el)
	{
		BX.toggle(el);
	}

	var controlsBlock = BX(type + '_block');
	if (controlsBlock)
	{
		BX.toggle(controlsBlock);
	}

	el = BX(type + "_lbl");

	if (el)
	{
		el.innerHTML = BX.message('sonetF_' + type + (chk ? '_on' : '_off'));
	}
};

BX.BXSF.submitForm = function() {
	if (!BX('sonet-features-form'))
	{
		return;
	}

	BX.SocialnetworkUICommon.hideError(this.errorBlock);
	BX.SocialnetworkUICommon.showButtonWait(BX('sonet_group_features_form_button_submit'));

	BX.ajax.submitAjax(
		document.forms['sonet-features-form'],
		{
			url: BX('sonet-features-form').getAttribute('action'),
			method: 'POST',
			dataType: 'json',
			onsuccess: BX.delegate(function(responseData) {
				BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_features_form_button_submit'));

				if (
					typeof responseData.MESSAGE != 'undefined'
					&& responseData.MESSAGE == 'SUCCESS'
					&& typeof responseData.URL != 'undefined'
				)
				{
					if (this.iframe)
					{
						BX.SidePanel.Instance.close();
					}
					top.location.href = responseData.URL;
				}
				else if (
					typeof responseData.MESSAGE != 'undefined'
					&& responseData.MESSAGE == 'ERROR'
					&& typeof responseData.ERROR_MESSAGE != 'undefined'
					&& responseData.ERROR_MESSAGE.length > 0
				)
				{
					BX.SocialnetworkUICommon.showError(responseData["ERROR_MESSAGE"], this.errorBlock);
				}
			}, this),
			onfailure: BX.delegate(function(responseData) {
				BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_features_form_button_submit'));
				BX.SocialnetworkUICommon.showError(BX.message('SONET_C4_T_ERROR'), this.errorBlock);
			}, this)
		}
	);
};

})();