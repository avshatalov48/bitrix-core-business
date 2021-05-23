function resetForm(form, e) {
	var inputs = form.elements,
		i;
	for (i = 0; i < inputs.length; i++)
	{
		if (inputs[i].tagName == "SELECT")
		{
			inputs[i].selectedIndex = 0;
		}
		else if (inputs[i].tagName == "TEXTAREA")
		{
			inputs[i].value = '';
		}
		else
		{
			switch (inputs[i].type) {
				case 'text':
					inputs[i].value = '';
					break;
				case 'radio':
				case 'checkbox':
					inputs[i].checked = false;
					break;
			}
		}
	}
	return BX.PreventDefault(e);
}
