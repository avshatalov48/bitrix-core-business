__bitrixSalePush = function(params) {

	for(var key in params)
		if(params.hasOwnProperty(key))
			this[key] = params[key];
};

__bitrixSalePush.prototype.close = function()
{
	app.closeController({drop: true});
};

__bitrixSalePush.prototype.getSubs = function()
{
	var form = BX("mapp_edit_form_id"),
		subsToAll = BX("subs_2_all"),
		result = [];

	var bSubsToAll = subsToAll && subsToAll.value && subsToAll.value == 'Y' ? true : false;

	if(form && form.elements && form.elements["SUBS[]"])
	{
		for (var i = form.elements["SUBS[]"].length - 1; i >= 0; i--)
		{
			if(bSubsToAll || form.elements["SUBS[]"][i].checked)
				result[form.elements["SUBS[]"][i].value] = "Y";
			else
				result[form.elements["SUBS[]"][i].value] = "N";
		}
	}

	return result;
};

__bitrixSalePush.prototype.toggleSubsBlock = function()
{
	var subs = BX("subs_items_block_id");
	subs.style.display = subs.style.display === "none" ? "" : "none";
};

__bitrixSalePush.prototype.save = function()
{
	var subs = this.getSubs();

	var postData = {
		subs: subs,
		sessid: BX.bitrix_sessid(),
		action: 'save_subs'
	};

	app.showPopupLoader({text: BX.message("SMOP_JS_SAVING")+"..."});

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
				app.openLeft();
			}
			else if(result.ERROR)
			{
				app.alert({ text: 'ERROR: '+result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('SMOP_JS_SAVE_ERROR')+' !result.'});
			}
		},
		onfailure: function()
		{
			app.alert({ text: BX.message('SMOP_JS_SAVE_ERROR')+' onfailure.'});
		}
	});
};