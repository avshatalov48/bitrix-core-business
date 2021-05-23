__BitrixCloudMobMonEdt = function(params)
{
	for(var key in params)
		this[key] = params[key];
};

__BitrixCloudMobMonEdt.prototype.getFields = function(form)
{
	if(!form || !form.elements || !form.elements.length)
		return false;

	aResult = [];

	for(var i=form.elements.length-1; i>=0; i--)
	{
		if(form.elements[i].type == 'checkbox' || form.elements[i].type == 'radio')
			if(!form.elements[i].checked)
				continue;

		if(!aResult[form.elements[i].name])
			aResult[form.elements[i].name] = [];

		aResult[form.elements[i].name].push(form.elements[i].value);
	}

	if(aResult["TESTS[]"])
	{
		aResult["TESTS"] = aResult["TESTS[]"];
		delete(aResult["TESTS[]"]);
	}

	if(aResult["EMAILS[]"])
	{
		aResult["EMAILS"] = aResult["EMAILS[]"];
		delete(aResult["EMAILS[]"]);
	}

	if(aResult["LANG"])
		aResult["LANG"] = aResult["LANG"].pop();

	if(aResult["IS_HTTPS"])
		aResult["IS_HTTPS"] = aResult["IS_HTTPS"].pop();

	return aResult;
};
