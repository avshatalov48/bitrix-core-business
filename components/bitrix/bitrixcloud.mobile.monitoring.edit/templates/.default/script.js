BX.namespace('BX.BitrixCloud');
BX.BitrixCloud.MobileMonitorEdit = function()
{
	this.getFields = function(form)
	{
		if (!form || !form.elements || form.elements.length === 0)
		{
			return false;
		}

		const result = [];

		for (let i = form.elements.length - 1; i >= 0; i--)
		{
			if (
				(form.elements[i].type === 'checkbox' || form.elements[i].type === 'radio')
				&& (!form.elements[i].checked)
			)
			{
				continue;
			}

			if (!result[form.elements[i].name])
			{
				result[form.elements[i].name] = [];
			}

			result[form.elements[i].name].push(form.elements[i].value);
		}

		if (result['TESTS[]'])
		{
			result.TESTS = result['TESTS[]'];
			delete result['TESTS[]'];
		}

		if (result['EMAILS[]'])
		{
			result.EMAILS = result['EMAILS[]'];
			delete result['EMAILS[]'];
		}

		if (result.LANG)
		{
			result.LANG = result.LANG.pop();
		}

		if (result.IS_HTTPS)
		{
			result.IS_HTTPS = result.IS_HTTPS.pop();
		}

		return result;
	};
};
