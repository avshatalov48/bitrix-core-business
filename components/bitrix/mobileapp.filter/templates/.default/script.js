/**********Filter************/
__MAAdminFilter = function(params) {
	/*filterFields{VALUE:,NAME:,TYPE:}
	applyEvent, filterId, url, ajaxUrl, fieldEditUrl, selectAllConst*/
	for(var key in params)
		this[key] = params[key];

	this.curFieldId = "";
	this.fieldFilter = false;
	this.optionParams = {
			moduleId: 'mobileapp',
			optionName: 'filter_'+this.filterId
		};
	this.loadedScripts = {};
	this.flagLoadingScript = false;
	this.interval = {};
};

__MAAdminFilter.prototype.save = function()
{
	if(!this.optionParams || !this.optionParams.moduleId || !this.optionParams.optionName)
		return;

	BX.userOptions.del(this.optionParams.moduleId, this.optionParams.optionName);

	var value = '';

	for(var fieldId in this.filterFields)
	{
		if(this.filteredFields[fieldId])
		{
			value = this.getFieldValue(fieldId);
			this.filterFields[fieldId].VALUE = value;
			BX.userOptions.save(this.optionParams.moduleId, this.optionParams.optionName, fieldId, value);
		}
	}
};

__MAAdminFilter.prototype.getFieldValue = function(fieldId)
{
	var result ="",	fieldName, form;

	if(this.filterFields[fieldId].TYPE == "TEXT" || this.filterFields[fieldId].TYPE == "DATE")
	{
		var field = BX("field_id_"+fieldId);

		if(field && field.value)
			result = field.value;
	}
	else if(this.filterFields[fieldId].TYPE == "ONE_SELECT")
	{
		fieldName = "field_name_"+fieldId;
		form = BX("mapp_filter_form_id");

		if(form && form.elements && form.elements[fieldName])
		{
			for(var s=0, sl=form.elements[fieldName].length; s<sl; s++)
			{
				var el = form.elements[fieldName][s];

				if(el.checked)
				{
					if(el.value == this.selectAllConst)
						result = '';
					else
						result = el.value;

					break;
				}
			}
		}
	}
	else if(this.filterFields[fieldId].TYPE == "MULTI_SELECT")
	{
		form = BX("mapp_filter_form_id");
		fieldName = "field_name_"+fieldId;
		result = [];

		if(form && form.elements && form.elements[fieldName])
		{
			for(var s=0, sl=form.elements[fieldName].length; s<sl; s++)
			{
				var el = form.elements[fieldName][s];

				if(el.checked && el.value)
					result.push(el.value);
			}
		}
	}

	return result;
};

__MAAdminFilter.prototype.setFieldValue = function(fieldId, value)
{
	if(this.filterFields[fieldId].TYPE == "TEXT" || this.filterFields[fieldId].TYPE == "DATE")
	{
		var field = BX("field_id_"+fieldId);

		if(field && field.value)
			field.value = value;
	}
	else if(this.filterFields[fieldId].TYPE == "ONE_SELECT" || this.filterFields[fieldId].TYPE == "MULTI_SELECT")
	{
		var form = BX("mapp_filter_form_id"),
			fieldName = "field_name_"+fieldId;

		if(form && form.elements && form.elements[fieldName])
		{
			for(var s=0, sl=form.elements[fieldName].length; s<sl; s++)
			{
				var el = form.elements[fieldName][s];

				if((el.value == value && el.checked === false)||( el.value != value && el.checked === true) && el.id)
				{
					BX.onCustomEvent('onMappEditEltItemClick', [{id: el.id}]);
				}
			}
		}
	}
};

__MAAdminFilter.prototype.apply = function()
{
	var _this = this;
	this.save();

	setTimeout( function() {
		app.onCustomEvent(_this.applyEvent);
		app.closeController();
	}, 10);
};

__MAAdminFilter.prototype.reset = function()
{
	for(var i in this.filteredFields)
		this.setFieldValue(i, "");

	this.save();
};


__MAAdminFilter.prototype.showFieldsList = function()
{
	app.showModalDialog({ url: this.url+"&show_fields_list=Y" });
};

__MAAdminFilter.prototype.getHtmlAjax = function(visFields)
{
	if(!visFields)
		return false;

	var visFieldsFullInfo = {};

	for (var i in visFields)
		if(this.filterFields[i])
			visFieldsFullInfo[i] = this.filterFields[i];

	var _this = this,
		postData = {
		fields: visFieldsFullInfo,
		filter_id: this.filterId,
		sessid: BX.bitrix_sessid(),
		action: 'get_fields_html'
	};

	app.showPopupLoader({text: BX.message("MOBILE_APP_FILTER_SAVING")+"..."});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result)
		{
			if(result && result.HTML)
			{
				var fltContent = BX("mapp_filter_content");

				if(fltContent)
					fltContent.innerHTML = result.HTML;

				var expr = /<script.*>[\s\S]*?<\/script>/gim;
				var script = result.HTML.match(expr),
					tmpScr = "";

				if(script !== null && script.length > 0)
					for (var i = 0, l = script.length - 1; i <= l; i++)
						_this.interval[i] = setInterval( _this.scriptExec(i, script[i], _this), 200);

				_this.filteredFields = visFieldsFullInfo;
				_this.save();

				app.hidePopupLoader();
			}
			else if(result.ERROR)
			{
				app.hidePopupLoader();
				app.alert({ text: 'getHtmlAjax() ERROR: '+result.ERROR });
			}
			else
			{
				app.hidePopupLoader();
				app.alert({ text: 'getHtmlAjax() !result.'});
			}
		},
		onfailure: function()
		{
			app.hidePopupLoader();
			app.alert({ text: 'getHtmlAjax() onfailure.'});
		}
	});
};

__MAAdminFilter.prototype.scriptExec = function(iCounter, script, _this)
{
	return function()
	{
		if(!_this.flagLoadingScript)
		{
			try
			{
				tmpScr = script.replace(/<script.*>/igm,"");
				tmpScr = tmpScr.replace(/<\/script>/igm,"");
				eval(tmpScr);
				clearInterval(_this.interval[iCounter]);
				delete(_this.interval[iCounter]);
			}
			catch(e)
			{
				//probably needed js not loaded yet
			}
		}
	};
};

__MAAdminFilter.prototype.onFieldValueChange = function(params)
{
	if(params.filterId != this.filterId)
		return true;

	this.setField(params.fieldId, params);

	var optSaveDelay = BX.userOptions.delay;
	BX.userOptions.delay = 100;
	this.save();
	BX.userOptions.delay = optSaveDelay;
};

__MAAdminFilter.prototype.showFieldEdit = function(fieldId)
{
	app.showModalDialog({
		url: this.url+"&edit_field_value=Y&filter_id="+this.filterId+"&field_id="+fieldId
	});
};

__MAAdminFilter.prototype.getDatePickerHtml = function(domObj)
{
	if(domObj && domObj.value !== undefined)
	{
		var startDate,
			today = new Date();

		if(domObj.value)
			startDate = domObj.value;
		else
			startDate = BX.formatDate(today, "DD.MM.YYYY");

		app.showDatePicker({
			start_date: startDate,
			format: 'dd.MM.yyyy',
			type: 'date',
			callback: function(strDate) { domObj.value = strDate; }
		});
	}
};

__MAAdminFilter.prototype.loadScript = function (url)
{
	this.flagLoadingScript = true;

	if(!this.loadedScripts[url])
	{
		var el = document.createElement("script");
		el.src = url;
		el.type="text/javascript";
		BX.findChild(document, {tagName:'head'},true).appendChild(el);
		this.loadedScripts[url] = true;
	}

	this.flagLoadingScript = false;
};
