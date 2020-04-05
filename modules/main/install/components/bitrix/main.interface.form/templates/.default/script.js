function BxInterfaceForm(name, aTabs)
{
	var _this = this;
	this.name = name;
	this.aTabs = aTabs;
	this.bExpandTabs = false;
	this.vars = {};
	this.oTabsMeta = {};
	this.aTabsEdit = [];
	this.oFields = {};
	this.menu = new PopupMenu('bxFormMenu_'+this.name, 1010);
	this.settingsMenu = [];
	this.tabSettingsWnd = null;
	this.fieldSettingsWnd = null;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById('inner_tab_'+tab_id);
		if(div.style.display != 'none')
			return;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById('inner_tab_'+this.aTabs[i]);
			if(tab.style.display != 'none')
			{
				this.ShowTab(this.aTabs[i], false);
				tab.style.display = 'none';
				break;
			}
		}

		this.ShowTab(tab_id, true);
		div.style.display = 'block';

		var hidden = document.getElementById(this.name+'_active_tab');
		if(hidden)
			hidden.value = tab_id;
	};

	this.ShowTab = function(tab_id, on)
	{
		var sel = (on? '-selected':'');
		document.getElementById('tab_cont_'+tab_id).className = 'bx-tab-container'+sel;
		document.getElementById('tab_left_'+tab_id).className = 'bx-tab-left'+sel;
		document.getElementById('tab_'+tab_id).className = 'bx-tab'+sel;
		document.getElementById('tab_right_'+tab_id).className = 'bx-tab-right'+sel;
	};

	this.HoverTab = function(tab_id, on)
	{
		var tab = document.getElementById('tab_'+tab_id);
		if(tab.className == 'bx-tab-selected')
			return;

		document.getElementById('tab_left_'+tab_id).className = (on? 'bx-tab-left-hover':'bx-tab-left');
		tab.className = (on? 'bx-tab-hover':'bx-tab');
		var tab_right = document.getElementById('tab_right_'+tab_id);
		tab_right.className = (on? 'bx-tab-right-hover':'bx-tab-right');
	};

	this.ShowDisabledTab = function(tab_id, disabled)
	{
		var tab = document.getElementById('tab_cont_'+tab_id);
		if(disabled)
		{
			tab.className = 'bx-tab-container-disabled';
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		else
		{
			tab.className = 'bx-tab-container';
			tab.onclick = function(){_this.SelectTab(tab_id);};
			tab.onmouseover = function(){_this.HoverTab(tab_id, true);};
			tab.onmouseout = function(){_this.HoverTab(tab_id, false);};
		}
	};

	this.ToggleTabs = function(bSkipSave)
	{
		this.bExpandTabs = !this.bExpandTabs;

		var a = document.getElementById('bxForm_'+this.name+'_expand_link');
		if (!a)
		{
			return;
		}

		a.title = (this.bExpandTabs? this.vars.mess.collapseTabs : this.vars.mess.expandTabs);
		a.className = (this.bExpandTabs? a.className.replace(/\s*bx-down/ig, ' bx-up') : a.className.replace(/\s*bx-up/ig, ' bx-down'));

		var div;
		for(var i in this.aTabs)
		{
			var tab_id = this.aTabs[i];
			this.ShowTab(tab_id, false);
			this.ShowDisabledTab(tab_id, this.bExpandTabs);
			div = document.getElementById('inner_tab_'+tab_id);
			div.style.display = (this.bExpandTabs? 'block':'none');
		}
		if(!this.bExpandTabs)
		{
			this.ShowTab(this.aTabs[0], true);
			div = document.getElementById('inner_tab_'+this.aTabs[0]);
			div.style.display = 'block';
		}
		if(bSkipSave !== true)
			BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?FORM_ID='+this.name+'&action=expand&expand='+(this.bExpandTabs? 'Y':'N')+'&sessid='+this.vars.sessid);
	};

	this.SetTheme = function(menuItem, theme)
	{
		BX.loadCSS(this.vars.template_path+'/themes/'+theme+'/style.css');

		var themeMenu = this.menu.GetMenuByItemId(menuItem.id);
		themeMenu.SetAllItemsIcon('');
		themeMenu.SetItemIcon(menuItem, 'checked');

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.php?FORM_ID='+this.name+'&GRID_ID='+this.vars.GRID_ID+'&action=settheme&theme='+theme+'&sessid='+this.vars.sessid);
	};

	this.ShowSettings = function()
	{
		var bCreated = false;
		if(!window['formSettingsDialog'+this.name])
		{
			window['formSettingsDialog'+this.name] = new BX.CDialog({
				'content':'<form name="form_settings_'+this.name+'"></form>',
				'title': this.vars.mess.settingsTitle,
				'width': this.vars.settingWndSize.width,
				'height': this.vars.settingWndSize.height,
				'resize_id': 'InterfaceFormSettingWnd'
			});
			bCreated = true;
		}

		window['formSettingsDialog'+this.name].ClearButtons();
		window['formSettingsDialog'+this.name].SetButtons([
			{
				'title': this.vars.mess.settingsSave,
				'action': function()
				{
					_this.SaveSettings();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);

		window['formSettingsDialog'+this.name].Show();

		var form = document['form_settings_'+this.name];

		if(bCreated)
			form.appendChild(BX('form_settings_'+this.name));

		//editable data
		var i;
		this.aTabsEdit = [];
		for(i in this.oTabsMeta)
		{
			var fields = [];
			for(var j in this.oTabsMeta[i].fields)
				fields[fields.length] = BX.clone(this.oTabsMeta[i].fields[j]);
			this.aTabsEdit[this.aTabsEdit.length] = BX.clone(this.oTabsMeta[i]);
			this.aTabsEdit[this.aTabsEdit.length-1].fields = fields;
		}

		//tabs
		jsSelectUtils.deleteAllOptions(form.tabs);
		for(i in this.aTabsEdit)
			form.tabs.options[form.tabs.length] = new Option(this.aTabsEdit[i].name, this.aTabsEdit[i].id, false, false);

		//fields
		form.tabs.selectedIndex = 0;
		this.OnSettingsChangeTab();

		//available fields
		this.aAvailableFields = BX.clone(this.oFields);
		jsSelectUtils.deleteAllOptions(form.all_fields);
		for(i in this.aAvailableFields)
			form.all_fields.options[form.all_fields.length] = new Option(this.aAvailableFields[i].name, this.aAvailableFields[i].id, false, false);

		jsSelectUtils.sortSelect(form.all_fields);

		this.HighlightSections(form.all_fields);

		this.ProcessButtons();

		form.tabs.focus();
	};

	this.OnSettingsChangeTab = function()
	{
		var form = document['form_settings_'+this.name];
		var index = form.tabs.selectedIndex;

		jsSelectUtils.deleteAllOptions(form.fields);
		for(var i in this.aTabsEdit[index].fields)
		{
			var opt = new Option(this.aTabsEdit[index].fields[i].name, this.aTabsEdit[index].fields[i].id, false, false);
			if(this.aTabsEdit[index].fields[i].type == 'section')
				opt.className = 'bx-section';
			form.fields.options[form.fields.length] = opt;
		}

		this.ProcessButtons();
	};

	this.TabMoveUp = function()
	{
		var form = document['form_settings_'+this.name];
		var index = form.tabs.selectedIndex;

		if(index > 0)
		{
			var tab1 = BX.clone(this.aTabsEdit[index]);
			this.aTabsEdit[index] = BX.clone(this.aTabsEdit[index-1]);
			this.aTabsEdit[index-1] = tab1;
		}
		jsSelectUtils.moveOptionsUp(form.tabs);
	};

	this.TabMoveDown = function()
	{
		var form = document['form_settings_'+this.name];
		var index = form.tabs.selectedIndex;

		if(index < form.tabs.length-1)
		{
			var tab1 = BX.clone(this.aTabsEdit[index]);
			this.aTabsEdit[index] = BX.clone(this.aTabsEdit[index+1]);
			this.aTabsEdit[index+1] = tab1;
		}
		jsSelectUtils.moveOptionsDown(form.tabs);
	};

	this.TabEdit = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		if(tabIndex < 0)
			return;

		this.ShowTabSettings(this.aTabsEdit[tabIndex],
			function()
			{
				var frm = document['tab_settings_'+_this.name];
				_this.aTabsEdit[tabIndex].name = frm.tab_name.value;
				_this.aTabsEdit[tabIndex].title = frm.tab_title.value;

				form.tabs[tabIndex].text = frm.tab_name.value;
			}
		);
	};

	this.TabAdd = function()
	{
		this.ShowTabSettings({'name':'', 'title':''},
			function()
			{
				var tab_id = 'tab_'+Math.round(Math.random()*1000000);

				var frm = document['tab_settings_'+_this.name];
				_this.aTabsEdit[_this.aTabsEdit.length] = {
					'id': tab_id,
					'name': frm.tab_name.value,
					'title': frm.tab_title.value,
					'fields': []
				};

				var form = document['form_settings_'+_this.name];
				form.tabs[form.tabs.length] = new Option(frm.tab_name.value, tab_id, true, true);
				_this.OnSettingsChangeTab();
			}
		);
	};

	this.TabDelete = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		if(tabIndex < 0)
			return;

		//place to available fields before delete
		var i;
		for(i in this.aTabsEdit[tabIndex].fields)
		{
			this.aAvailableFields[this.aTabsEdit[tabIndex].fields[i].id] = this.aTabsEdit[tabIndex].fields[i];
			jsSelectUtils.addNewOption(form.all_fields, this.aTabsEdit[tabIndex].fields[i].id, this.aTabsEdit[tabIndex].fields[i].name, true, false);
		}

		this.HighlightSections(form.all_fields);

		this.aTabsEdit = BX.util.deleteFromArray(this.aTabsEdit, tabIndex);
		form.tabs.remove(tabIndex);

		if(form.tabs.length > 0)
		{
			i = (tabIndex < form.tabs.length? tabIndex : form.tabs.length-1);
			form.tabs[i].selected = true;
			this.OnSettingsChangeTab();
		}
		else
		{
			jsSelectUtils.deleteAllOptions(form.fields);
			this.ProcessButtons();
		}
	};

	this.ShowTabSettings = function(data, action)
	{
		var wnd = this.tabSettingsWnd;
		if(!wnd)
		{
			this.tabSettingsWnd = wnd = new BX.CDialog({
				'content':'<form name="tab_settings_'+this.name+'">'+
					'<table width="100%">'+
					'<tr>'+
					'<td width="50%" align="right">'+this.vars.mess.tabSettingsName+'</td>'+
					'<td><input type="text" name="tab_name" size="30" value="" style="width:90%"></td>'+
					'</tr>'+
					'<tr>'+
					'<td align="right">'+this.vars.mess.tabSettingsCaption+'</td>'+
					'<td><input type="text" name="tab_title" size="30" value="" style="width:90%"></td>'+
					'</tr>'+
					'</table>'+
					'</form>',
				'title': this.vars.mess.tabSettingsTitle,
				'width': this.vars.tabSettingWndSize.width,
				'height': this.vars.tabSettingWndSize.height,
				'resize_id': 'InterfaceFormTabSettingWnd'
			});
		}
		wnd.ClearButtons();
		wnd.SetButtons([
			{
				'title': this.vars.mess.tabSettingsSave,
				'action': function(){
					action();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);
		wnd.Show();

		var form = document['tab_settings_'+this.name];
		form.tab_name.value = data.name;
		form.tab_title.value = data.title;
		form.tab_name.focus();
	};

	this.ShowFieldSettings = function(data, action)
	{
		var wnd = this.fieldSettingsWnd;
		if(!wnd)
		{
			this.fieldSettingsWnd = wnd = new BX.CDialog({
				'content':'<form name="field_settings_'+this.name+'">'+
					'<table width="100%">'+
					'<tr>'+
					'<td width="50%" align="right" id="field_name_'+this.name+'"></td>'+
					'<td><input type="text" name="field_name" size="30" value="" style="width:90%"></td>'+
					'</tr>'+
					'</table>'+
					'</form>',
				'width': this.vars.fieldSettingWndSize.width,
				'height': this.vars.fieldSettingWndSize.height,
				'resize_id': 'InterfaceFormFieldSettingWnd'
			});
		}

		wnd.SetTitle(data.type && data.type == 'section'? this.vars.mess.sectSettingsTitle : this.vars.mess.fieldSettingsTitle);
		BX('field_name_'+this.name).innerHTML = (data.type && data.type == 'section'? this.vars.mess.sectSettingsName : this.vars.mess.fieldSettingsName);

		wnd.ClearButtons();
		wnd.SetButtons([
			{
				'title': this.vars.mess.tabSettingsSave,
				'action': function(){
					action();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);
		wnd.Show();

		var form = document['field_settings_'+this.name];
		form.field_name.value = data.name;
		form.field_name.focus();
	};

	this.FieldEdit = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;
		var fieldIndex = form.fields.selectedIndex;

		if(tabIndex < 0 || fieldIndex < 0)
			return;

		this.ShowFieldSettings(this.aTabsEdit[tabIndex].fields[fieldIndex],
			function()
			{
				var frm = document['field_settings_'+_this.name];
				_this.aTabsEdit[tabIndex].fields[fieldIndex].name = frm.field_name.value;

				form.fields[fieldIndex].text = frm.field_name.value;
			}
		);
	};

	this.FieldAdd = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		if(tabIndex < 0)
			return;

		this.ShowFieldSettings({'name':'', 'type':'section'},
			function()
			{
				var field_id = 'field_'+Math.round(Math.random()*1000000);
				var frm = document['field_settings_'+_this.name];
				_this.aTabsEdit[tabIndex].fields[_this.aTabsEdit[tabIndex].fields.length] = {
					'id': field_id,
					'name': frm.field_name.value,
					'type': 'section'
				};
				var opt = new Option(frm.field_name.value, field_id, true, true);
				opt.className = 'bx-section';
				form.fields[form.fields.length] = opt;
				_this.ProcessButtons();
			}
		);
	};

	this.FieldsMoveUp = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		var n = form.fields.length;
		for(var i=0; i<n; i++)
		{
			if(form.fields[i].selected && i>0 && form.fields[i-1].selected == false)
			{
				var field1 = BX.clone(this.aTabsEdit[tabIndex].fields[i]);
				this.aTabsEdit[tabIndex].fields[i] = BX.clone(this.aTabsEdit[tabIndex].fields[i-1]);
				this.aTabsEdit[tabIndex].fields[i-1] = field1;

				var option1 = new Option(form.fields[i].text, form.fields[i].value);
				var option2 = new Option(form.fields[i-1].text, form.fields[i-1].value);
				option1.className = form.fields[i].className;
				option2.className = form.fields[i-1].className;
				form.fields[i] = option2;
				form.fields[i].selected = false;
				form.fields[i-1] = option1;
				form.fields[i-1].selected = true;
			}
		}
	};

	this.FieldsMoveDown = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		var n = form.fields.length;
		for(var i=n-1; i>=0; i--)
		{
			if(form.fields[i].selected && i<n-1 && form.fields[i+1].selected == false)
			{
				var field1 = BX.clone(this.aTabsEdit[tabIndex].fields[i]);
				this.aTabsEdit[tabIndex].fields[i] = BX.clone(this.aTabsEdit[tabIndex].fields[i+1]);
				this.aTabsEdit[tabIndex].fields[i+1] = field1;

				var option1 = new Option(form.fields[i].text, form.fields[i].value);
				var option2 = new Option(form.fields[i+1].text, form.fields[i+1].value);
				option1.className = form.fields[i].className;
				option2.className = form.fields[i+1].className;
				form.fields[i] = option2;
				form.fields[i].selected = false;
				form.fields[i+1] = option1;
				form.fields[i+1].selected = true;
			}
		}
	};

	this.FieldsAdd = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		if(tabIndex == -1)
			return;

		var fields = this.aTabsEdit[tabIndex].fields;

		var n = form.all_fields.length, i;
		for(i=0; i<n; i++)
			if(form.all_fields[i].selected)
				fields[fields.length] = {
					'id': form.all_fields[i].value,
					'name': form.all_fields[i].text,
					'type': this.aAvailableFields[form.all_fields[i].value].type
				};

		jsSelectUtils.addSelectedOptions(form.all_fields, form.fields, false, false);
		jsSelectUtils.deleteSelectedOptions(form.all_fields);

		for(i=0, n=form.fields.length; i<n; i++)
			if(fields[i].type == 'section')
				form.fields[i].className = 'bx-section';

		this.ProcessButtons();
	};

	this.FieldsDelete = function()
	{
		var form = document['form_settings_'+this.name];
		var tabIndex = form.tabs.selectedIndex;

		if(tabIndex == -1)
			return;

		var n = form.fields.length;
		var delta = 0;
		for(var i=0; i<n; i++)
		{
			if(form.fields[i].selected)
			{
				this.aAvailableFields[form.fields[i].value] = this.aTabsEdit[tabIndex].fields[i-delta];
				this.aTabsEdit[tabIndex].fields = BX.util.deleteFromArray(this.aTabsEdit[tabIndex].fields, i-delta);
				delta++;
			}
		}

		jsSelectUtils.addSelectedOptions(form.fields, form.all_fields, false, true);
		jsSelectUtils.deleteSelectedOptions(form.fields);

		this.HighlightSections(form.all_fields);

		this.ProcessButtons();
	};

	this.ProcessButtons = function()
	{
		var form = document['form_settings_'+this.name];

		form.add_btn.disabled = (form.all_fields.selectedIndex == -1 || form.tabs.selectedIndex == -1);
		form.del_btn.disabled = form.up_btn.disabled = form.down_btn.disabled = form.field_edit_btn.disabled = (form.fields.selectedIndex == -1);
		form.tab_up_btn.disabled = form.tab_down_btn.disabled = form.tab_edit_btn.disabled = form.tab_del_btn.disabled = form.field_add_btn.disabled = (form.tabs.selectedIndex == -1);
	};

	this.HighlightSections = function(el)
	{
		for(var i=0, n=el.length; i<n; i++)
			if(this.aAvailableFields[el[i].value].type == 'section')
				el[i].className = 'bx-section';
	};

	this.SaveSettings = function()
	{
		var data = {
			'FORM_ID': this.name,
			'action': 'savesettings',
			'sessid': this.vars.sessid,
			'tabs': this.aTabsEdit
		};
		var form = document['form_settings_'+this.name];
		if(form['set_default_settings'])
		{
			data.set_default_settings = (form.set_default_settings.checked? 'Y':'N');
			data.delete_users_settings = (form.delete_users_settings.checked? 'Y':'N');
		}
		BX.ajax.post('/bitrix/components'+_this.vars.component_path+'/settings.php', data, function(){_this.Reload()});
	};

	this.EnableSettings = function(enabled)
	{
		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?FORM_ID='+this.name+'&action=enable&enabled='+(enabled? 'Y':'N')+'&sessid='+this.vars.sessid, function(){_this.Reload()});
	};

	this.Reload = function()
	{
		if(this.vars.ajax.AJAX_ID != '')
			BX.ajax.insertToNode(this.vars.current_url+(this.vars.current_url.indexOf('?') == -1? '?':'&')+'bxajaxid='+this.vars.ajax.AJAX_ID, 'comp_'+this.vars.ajax.AJAX_ID);
		else
			window.location = window.location.href;
	};
}

