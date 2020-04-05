__bitrixCloudPush = function(params) {

	for(var key in params)
		this[key] = params[key];
};

__bitrixCloudPush.prototype.makeFastButton = function(Id, url)
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

__bitrixCloudPush.prototype.close = function()
{
	if(app.enableInVersion(8))
		app.closeModalDialog();
	else
		app.closeController({drop: true});
};


__bitrixCloudPush.prototype.getOptions = function()
{
	var form = BX("mapp_edit_form_id"),
		result = [],
		optCount;

	if(form && form.elements && form.elements["SUBSCRIBE"] && form.elements["SUBSCRIBE"].value)
		result["SUBSCRIBE"] = form.elements["SUBSCRIBE"].value;

	return result;
};

__bitrixCloudPush.prototype.save = function()
{
	var options = this.getOptions(),
		_this = this;

	var postData = {
		options: options,
		domain: this.domain,
		sessid: BX.bitrix_sessid(),
		action: 'save_options'
	};

	app.showPopupLoader({text: BX.message("BCMMP_JS_SAVING")+"..."});

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
				app.alert({ text: BX.message('BCMMP_JS_SAVE_ERROR')+' !result.'});
			}
		},
		onfailure: function()
		{
			app.alert({ text: BX.message('BCMMP_JS_SAVE_ERROR')+' onfailure.'});
		}
	});
};