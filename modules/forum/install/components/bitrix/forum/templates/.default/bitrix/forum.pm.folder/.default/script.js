if (typeof oForum != "object")
	var oForum = {};
if (typeof oForum["selectors"] != "object")
	oForum["selectors"] = {};

function FSelectAll(oObj, name, bRestore)
{
	if (typeof oObj != "object" || oObj == null || !name)
		return false;
	var sSelectorName = 'all_' + name.replace(/[^a-z0-9]/ig, "_");
	bRestore = (bRestore == "Y" ? "Y" : "N");
	var items = oObj.form.getElementsByTagName('input');
	var iItemsChecked = [];
	if (items)
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
			items = [items];
		window.oForum["selectors"][sSelectorName] = {"count" : 0, "current" : 0};
		for (var ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == name))
				continue;
			window.oForum["selectors"][sSelectorName]["count"]++;
			if (bRestore == "Y" && items[ii].checked != oObj.checked)
				iItemsChecked.push(ii);
			onClickCheckbox(items[ii], (oObj.checked ? "Y" : "N"));
		}
		if (oObj.checked)
			window.oForum["selectors"][sSelectorName]["current"] = window.oForum["selectors"][sSelectorName]["count"];
		else
			window.oForum["selectors"][sSelectorName]["current"] = 0;

		if (iItemsChecked.length > 0)
		{
			for (var ii = 0; ii < iItemsChecked.length; ii++)
				onClickCheckbox(items[iItemsChecked[ii]], (oObj.checked ? "N" : "Y"));
			if (window.oForum["selectors"][sSelectorName]["current"] == window.oForum["selectors"][sSelectorName]["count"])
				oObj.form[sSelectorName].checked = true;
			else
				oObj.form[sSelectorName].checked = false;
		}
	}
	return;
}

function Validate(form)
{
	var bError = true;
	var items = form.getElementsByTagName('input');
	if (items)
	{
		
		if (!items.length || (typeof(items.length) == 'undefined'))
			items = [items];
		for (var ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == 'FID[]' && items[ii].checked && !items[ii].disabled))
				continue;
			bError = false;
			break;
		}
	}
	if (bError)
	{
		alert(oText['s_no_data']);
		return false;
	}
	if (form.action.value == 'delete')
		return confirm(oText['s_del']);
	else if (form.action.value == 'remove')
		return confirm(oText['s_del_mess']);
	return true;
}

function onClickCheckbox(oCheckBox, sSetValue)
{
	if (!oCheckBox)
		return false;
	var sSelectorName = 'all_' + oCheckBox.name.replace(/[^a-z0-9]/ig, "_");
	if (typeof(window.oForum["selectors"][sSelectorName]) != "object" || window.oForum["selectors"][sSelectorName] == null)
	{
		FSelectAll(oCheckBox.form[sSelectorName], oCheckBox.name, "Y");
		return true;
	}
	if (sSetValue == "N")
	{
		window.oForum["selectors"][sSelectorName]["current"]--;
		oCheckBox.checked = false;
	}
	else if (sSetValue == "Y")
	{
		window.oForum["selectors"][sSelectorName]["current"]++;
		oCheckBox.checked = true;
	}
	else
	{
		if (oCheckBox.checked)
			window.oForum["selectors"][sSelectorName]["current"]++;
		else
			window.oForum["selectors"][sSelectorName]["current"]--;
		
		if (oCheckBox.form[sSelectorName])
		{
			if (window.oForum["selectors"][sSelectorName]["current"] == window.oForum["selectors"][sSelectorName]["count"])
				oCheckBox.form[sSelectorName].checked = true;
			else
				oCheckBox.form[sSelectorName].checked = false;
		}
	}
}