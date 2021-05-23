function FSelectAll(oObj, bSet)
{
	if (typeof oObj != "object" || oObj == null)
		return false;
	var
		items = BX.findChild(oObj.form, {'tagName' : 'input', 'name' : 'SID[]', 'className' : 'forum-subscribe-checkbox'}, true, true),
		item = null;
	if (!items) return;
	bSet = (bSet == true);
	BX.adjust(oObj, {'props' : {'counter': items.length, 'current' : (bSet && !oObj.checked ? items.length : 0)}});
	if (bSet)
		while (item = items.pop())
			onClickCheckbox(item, true);
}

function onClickCheckbox(oCheckBox, bSetValue)
{
	if (!oCheckBox)
		return false;
	var oSelector = oCheckBox.form['all_SID__'];
	if (!oSelector.counter)
		FSelectAll(oSelector, false);
	if (bSetValue === true)
		oCheckBox.checked = oSelector.checked;
	oSelector.current = (oSelector.current + (oCheckBox.checked ? 1 : -1));
	if (bSetValue !== true)
		oSelector.checked = (oSelector.current == oSelector.counter);
}

function Validate(form)
{
	var
		bError = true,
		items = BX.findChild(form, {'tagName' : 'input', 'name' : 'SID[]', 'className' : 'forum-subscribe-checkbox'}, true, true),
		item = null;
	while (item = items.pop())
	{
		if (item.checked)
		{
			bError = false;
			break;
		}
	}
	if (bError)
	{
		alert(oText['s_no_data']);
		return false;
	}
	if (form.ACTION.value == 'DEL')
		return confirm(oText['s_del']);
	return true;
}