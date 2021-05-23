function switchFieldSet(fieldSetID)
{
	var obForm = document.forms['bx_user_profile_form'];
	var current_fieldset = obForm.current_fieldset.value;
	
	if (current_fieldset != fieldSetID)
	{
		var obCurrFieldset = document.getElementById('bx_sonet_fieldset_' + current_fieldset);
		var obNewFieldset = document.getElementById('bx_sonet_fieldset_' + fieldSetID);
		var obCurrSwitcher = document.getElementById('bx_sonet_switcher_' + current_fieldset);
		var obNewSwitcher = document.getElementById('bx_sonet_switcher_' + fieldSetID);
		
		if (obCurrFieldset && obNewFieldset)
		{
			obCurrFieldset.style.display = 'none';
			obNewFieldset.style.display = 'block';
			
			obCurrSwitcher.className = '';
			obNewSwitcher.className = 'bx-sonet-switcher-current';
			
			obForm.current_fieldset.value = fieldSetID;
		}
	}
	
	return;
}