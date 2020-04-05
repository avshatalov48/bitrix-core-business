function Validate(form)
{
	if (typeof(form) != "object" || form == null)
		return false;
	var oError = [];
	if (form.name.substr(0, 8) == 'MESSAGES')
	{
		var items = form.getElementsByTagName('input');
		if (items && typeof items == "object" )
		{
			if (!items.length || (typeof(items.length) == 'undefined'))
			{
				items = [items];
			}
			var bEmptyData = true;
			for (ii = 0; ii < items.length; ii++)
			{
				if (!(items[ii].type == "checkbox" && items[ii].name == 'message_id[]'))
					continue;
				if (items[ii].checked)
				{
					bEmptyData = false;
					break;
				}
			}
			if (bEmptyData)
				oError.push(BX.message("no_data"));
		}
	}
	if (form['ACTION'].value == '')
		oError.push(BX.message("no_action"));
	if (oError.length > 0)
	{
		alert(oError.join('\n'));
		return false;
	}
	if (form['ACTION'].value == 'DEL')
		return confirm(BX.message("cdms"));
	return true;
}