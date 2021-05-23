function SelectAllCheckBox(form_id, elements_name, control_checkbox_id)
{ 
	var checkbox_handle = document.getElementById(control_checkbox_id);
	for (i = 0; i < document.forms[form_id].elements.length; i++)
	{
		var item = document.forms[form_id].elements[i];
		if (item.name == elements_name)
		{
			item.checked = checkbox_handle.checked;
		}
	}
	return;
}
function SelectCheckBox(control_checkbox_id)
{
	var checkbox_handle = document.getElementById(control_checkbox_id);
	checkbox_handle.checked = false;
	return;
}
function DisableFolder(selectHandle)
{
	if (selectHandle.value == 'delete')
	{
		document.getElementById('FID_ID').disabled = true;
	}
	else
	{
		document.getElementById('FID_ID').disabled = false;
	}
}