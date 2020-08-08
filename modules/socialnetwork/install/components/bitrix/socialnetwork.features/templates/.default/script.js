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

		if (BX.SidePanel.Instance.getSliderByWindow(window))
		{
			BX.SidePanel.Instance.close();
		}
		else
		{
			var url = event.currentTarget.getAttribute('bx-url');
			if (BX.type.isNotEmptyString(url))
			{
				window.location=url;
			}
		}

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

	var actionURL = BX('sonet-features-form').getAttribute('action');
	if (
		document.forms['sonet-features-form'].elements.SONET_GROUP_ID
		&& parseInt(document.forms['sonet-features-form'].elements.SONET_GROUP_ID.value) > 0
	)
	{
		actionURL = BX.util.add_url_param(actionURL, {
			b24statAction: 'featuresSonetGroup'
		});
	}

	BX.ajax.submitAjax(
		document.forms['sonet-features-form'],
		{
			url: actionURL,
			method: 'POST',
			dataType: 'json',
			onsuccess: BX.delegate(function(responseData) {
				BX.SocialnetworkUICommon.hideButtonWait(BX('sonet_group_features_form_button_submit'));
				if (!BX.type.isNotEmptyString(responseData.MESSAGE))
				{
					return;
				}

				if (
					responseData.MESSAGE == 'SUCCESS'
					&& BX.type.isNotEmptyString(responseData.URL)
				)
				{
					if (this.iframe)
					{
						BX.SidePanel.Instance.close();
					}
					top.location.href = responseData.URL;
				}
				else if (
					responseData.MESSAGE == 'ERROR'
					&& BX.type.isNotEmptyString(responseData.ERROR_MESSAGE)
				)
				{
					BX.SocialnetworkUICommon.showError(responseData.ERROR_MESSAGE, this.errorBlock);
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