if (typeof oForum != "object" || !oForum)
	var oForum = {};
if (typeof oForum["selectors"] != "object" || !oForum["selectors"])
	oForum["selectors"] = {};
	

function FSelectAll(oObj, name)
{
	if (!oObj || oObj == null || !name)
		return false;
	var selector_name = name.replace(/[^a-z0-9]/ig, "_");
	if (oObj.name.length <= 0)
		oObj.name = 'all_' + selector_name;
	
	var items = oObj.form.getElementsByTagName('input');
	if (items && items != null)
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
		{
			items = [items];
		}
		
		window.oForum["selectors"][selector_name] = {
					"count" : 0,
					"current" : 0};
		for (ii=0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == name))
				continue;
			window.oForum["selectors"][selector_name]["count"]++;
			OnRowClick(items[ii].value, false, (oObj.checked ? "Y" : "N"));
		}
		if (oObj.checked)
			window.oForum["selectors"][selector_name]["current"] = window.oForum["selectors"][selector_name]["count"];
		else
			window.oForum["selectors"][selector_name]["current"] = 0;
	}
	return;
}

function ChangeAction(action, oObj)
{
	action = ((action == 'delete' || action == 'copy' || action == 'move') ? action : false);
	if (!action)
		return false;
	else if (!oObj || oObj == null)
		return false;
	else if (!oObj.form.action)
		return false;
	oObj.form.action.value = action;
	if 	(action != 'delete')
	{
		oObj.form['folder_id'].value = oObj.form['folder_id_' + action].value;
	}
	
	var items = document.getElementsByName('message[]');
	var data = [];
	if (items && items != null)
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
		{
			items = [items];
		}
		
		for (ii=0; ii < items.length; ii++)
		{
			if (items[ii].type == "checkbox" && items[ii].checked == true)
			{
				data.push(items[ii].value);
			}
		}
	}
	if (data.length <=0)
	{
		alert(oText['no_data']);
		return false;
	}
	else if (oObj.form.FID.value == 4 && action == 'delete' && !confirm(oText['del_message']))
	{
		return false;
	}

	if (oObj.form.submit)
		oObj.form.submit();
}

function OnRowClick(id, oRow, bChecked)
{
	id = parseInt(id);
	if (!(id > 0))
		return false;
	if (!oRow)
		oRow = document.getElementById('message_row_' + id);
	if (!oRow)
		return false;
	var oCheckBox = document.getElementById('message_id_' + id);
	if (!oCheckBox)
		return false;
	bChecked = ((bChecked == "Y" || bChecked == "N") ? bChecked : "U");
	if (bChecked == "U")
		bChecked = (oCheckBox.checked ? "N" : "Y");
	var selector_name = oCheckBox.name.replace(/[^a-z0-9]/ig, "_");
	if (!window.oForum["selectors"][selector_name])
		FSelectAll(oCheckBox.form['all_' + selector_name], oCheckBox.name);

	if (bChecked == "N")
	{
		window.oForum["selectors"][selector_name]["current"]--;
		oCheckBox.checked = false;
		oRow.className = oRow.className.replace(/checked/, '');
	}
	else
	{
		window.oForum["selectors"][selector_name]["current"]++;
		oCheckBox.checked = true;
		oRow.className += " checked ";
	}
	if (oCheckBox.form['all_' + selector_name])
	{
		if (window.oForum["selectors"][selector_name]["current"] == window.oForum["selectors"][selector_name]["count"])
			oCheckBox.form['all_' + selector_name].checked = true;
		else
			oCheckBox.form['all_' + selector_name].checked = false;
	}
}

function OnInputClick(oObj)
{
	if (!oObj)
		return false;
	oObj.checked = (!oObj.checked);
	return;
}