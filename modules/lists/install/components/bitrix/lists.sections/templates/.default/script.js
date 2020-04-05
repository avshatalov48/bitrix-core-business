function addNewSection(form_id, message)
{
	var btnOK = new BX.CWindowButton({
		'title': 'OK',
		'action': function()
		{
			BX.showWait();

			var _form = BX(form_id);
			var _act = BX('form_section_action');
			var _newn = BX('new_section_name');

			var _prompt = BX('prompt_section_name');

			if(_form && _act && _newn && _prompt)
			{
				if(_prompt.value.length > 0)
				{
					_act.value = 'add';
					_newn.value = _prompt.value;

					_form.submit();
				}
			}
			this.parentWindow.Close();
		}
	})

	if (null == window.lists_sections_obDialog)
	{
		window.lists_sections_obDialog = new BX.CDialog({
			content: '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr valign="top"><td width="50%" align="right">'+message + ':</td><td width="50%" align="left"><input type="text" size="35" id="prompt_section_name" value=""></td></tr></table>',
			buttons: [btnOK, BX.CDialog.btnCancel],
			width: 500,
			height: 150
		});
	}
	window.lists_sections_obDialog.Show();

	var inp = BX('prompt_section_name');
	inp.value = '';
	inp.focus();
	inp.select();
}

function renameSection(form_id, message, old_id, old_name)
{
	var btnOK = new BX.CWindowButton({
		'title': 'OK',
		'action': function()
		{
			BX.showWait();

			var _form = BX(form_id);
			var _act = BX('form_section_action');
			var _newn = BX('new_section_name');
			var _old = BX('old_section_id');

			var _prompt = BX('prompt_section_name');

			if(_form && _act && _newn && _old && _prompt)
			{
				if(_prompt.value.length > 0)
				{
					_act.value = 'rename';
					_newn.value = _prompt.value;
					_old.value = old_id;

					_form.submit();
				}
			}
			this.parentWindow.Close();
		}
	})

	if (null == window.lists_sections_obDialog)
	{
		window.lists_sections_obDialog = new BX.CDialog({
			content: '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr valign="top"><td width="50%" align="right">'+message + ':</td><td width="50%" align="left"><input type="text" size="35" id="prompt_section_name" value=""></td></tr></table>',
			buttons: [btnOK, BX.CDialog.btnCancel],
			width: 500,
			height: 150
		});
	}

	window.lists_sections_obDialog.Show();

	var inp = BX('prompt_section_name');
	inp.value = old_name;
	inp.focus();
	inp.select();
}