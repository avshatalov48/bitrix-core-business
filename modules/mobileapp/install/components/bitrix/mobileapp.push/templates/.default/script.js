__mobAppPush = function(params) {

	for(var key in params)
		this[key] = params[key];
};

__mobAppPush.prototype.makeFastButton = function(Id, url)
{
	var el = BX(Id);

	if(el)
	{
		new FastButton(el, function(){

			if(app.enableInVersion(8))
				app.showModalDialog({ url: url });
			else
				app.loadPageBlank({ url: url });

		}, false);
	}
};

__mobAppPush.prototype.close = function()
{
	if(app.enableInVersion(8))
		app.closeModalDialog();
	else
		app.closeController({drop: true});
};

__mobAppPush.prototype.getOptions = function()
{
	var form = BX("mapp_edit_form_id"),
		result = [],
		optCount;

	if(!form || !form.elements || !form.elements["OPTIONS[]"])
		return false;

	if(form.elements["OPTIONS[]"].length)
	{
		for(var i=form.elements["OPTIONS[]"].length-1; i>=0; i--)
		{
			if(form.elements["OPTIONS[]"][i].checked)
				result[form.elements["OPTIONS[]"][i].value] = 'Y';
			else
				result[form.elements["OPTIONS[]"][i].value] = 'N';
		}
	}
	else
	{
		if(form.elements["OPTIONS[]"].checked)
			result[form.elements["OPTIONS[]"].value] = 'Y';
		else
			result[form.elements["OPTIONS[]"].value] = 'N';
	}

	return result;
};

__mobAppPush.prototype.save = function()
{
	var options = this.getOptions(),
		_this = this;

	var postData = {
		options: options,
		path: this.path,
		sessid: BX.bitrix_sessid(),
		action: 'save_options'
	};

	app.showPopupLoader({text: BX.message("MOBILE_APP_SAVING")+"..."});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result)
		{
			app.hidePopupLoader();
			if(result && !result.ERROR)
			{
				_this.close();
			}
			else if(result.ERROR)
			{
				app.alert({ text: 'ERROR: '+result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('MOBILE_APP_SAVE_ERROR')+' !result.'});
			}
		},
		onfailure: function()
		{
			app.alert({ text: BX.message('MOBILE_APP_SAVE_ERROR')+' onfailure.'});
		}
	});
};