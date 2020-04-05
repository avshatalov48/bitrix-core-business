function SelectRow(row)
{
	if (row == null)
		return;

	if(row.className.match(/forum-row-selected/))
		row.className = row.className.replace(/\s*forum-row-selected/i, '');
	else
		row.className += ' forum-row-selected';
}

if (typeof oForum != "object")
	var oForum = {};
if (typeof oForum["topics"] != "object")
	oForum["topics"] = {};

function SelectRows(iIndex)
{
	oForum["topics"][iIndex] = (oForum["topics"][iIndex] != "Y" ? "Y" : "N");
	form = document.forms['TOPICS_' + iIndex];
	if (typeof(form) != "object" || form == null)
		return false;

	var items = form.getElementsByTagName('input');
	if (items && typeof items == "object" )
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
		{
			items = [items];
		}
		
		for (ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == 'TID[]'))
				continue;
			items[ii].checked = (oForum["topics"][iIndex] == "Y" ? true : false);
			var row = items[ii].parentNode.parentNode.parentNode;
			if (row == null)
				return;
			if (!items[ii].checked)
				row.className = row.className.replace(/\s*forum-row-selected/i, '');
			else if (!row.className.match(/forum-row-selected/))
				row.className += ' forum-row-selected';
		}
	}
}
function Validate(form)
{
	if (typeof(form) != "object" || form == null)
		return false;
	var oError = [];
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
			if (!(items[ii].type == "checkbox" && items[ii].name == 'TID[]'))
				continue;
			if (items[ii].checked)
			{
				bEmptyData = false;
				break;
			}
		}
		if (bEmptyData)
			oError.push(oText['empty_topics']);
	}
	if (form['ACTION'].value == '')
	{
		if (oError.length > 0)
			return false;
		oError.push(oText['empty_action']);
	}
	if (oError.length > 0)
	{
		alert(oError.join('\n'));
		return false;
	}
	return true;
}