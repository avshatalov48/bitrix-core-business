;(function(window) {

if (BX.adminSubList)
{
	return;
}

BX.adminSubList = function(table_id, params, list_url)
{
	BX.adminHistory.disable();
	BX.adminSubList.superclass.constructor.apply(this,[ table_id, params]);
	this.list_url = list_url;
};
BX.extend(BX.adminSubList, BX.adminList);

/* subelement methods */

BX.adminSubList.prototype._ActivateMainForm = function()
{
	if (!!this.MAIN_BUTTON_BTNSAVE)
	{
		this.MAIN_BUTTON_BTNSAVE.disabled = false;
	}
	if (!!this.MAIN_BUTTON_DONTSAVE)
	{
		this.MAIN_BUTTON_DONTSAVE.disabled = false;
	}
	if (!!this.MAIN_BUTTON_SAVE)
	{
		this.MAIN_BUTTON_SAVE.disabled = false;
	}
	if (!!this.MAIN_BUTTON_APPLY)
	{
		this.MAIN_BUTTON_APPLY.disabled = false;
	}
	if (!!this.MAIN_BUTTON_CANCEL)
	{
		this.MAIN_BUTTON_CANCEL.disabled = false;
	}
	if (!!this.MAIN_BUTTON_SAVE_ADD)
	{
		this.MAIN_BUTTON_SAVE_ADD.disabled = false;
	}
};

BX.adminSubList.prototype._DeActivateMainForm = function()
{
	if (!!this.MAIN_BUTTON_BTNSAVE)
	{
		this.MAIN_BUTTON_BTNSAVE.disabled = true;
	}
	if (!!this.MAIN_BUTTON_DONTSAVE)
	{
		this.MAIN_BUTTON_DONTSAVE.disabled = true;
	}
	if (!!this.MAIN_BUTTON_SAVE)
	{
		this.MAIN_BUTTON_SAVE.disabled = true;
	}
	if (!!this.MAIN_BUTTON_APPLY)
	{
		this.MAIN_BUTTON_APPLY.disabled = true;
	}
	if (!!this.MAIN_BUTTON_CANCEL)
	{
		this.MAIN_BUTTON_CANCEL.disabled = true;
	}
	if (!!this.MAIN_BUTTON_SAVE_ADD)
	{
		this.MAIN_BUTTON_SAVE_ADD.disabled = true;
	}
};

BX.adminSubList.prototype.FormSubmit = function()
{
	var obj = null,
		boolSend,
		reqdata,
		i;

	if (!!this.FORM)
	{
		obj = this.FORM.getElementsByTagName('input');
		if (!!obj && !!obj.length)
		{
			boolSend = false;
			reqdata = {};
			for (i = 0; i < obj.length; i++)
			{
				if ('SUB_ID[]' === obj[i].name)
				{
					if (obj[i].checked)
					{
						boolSend = true;
						if (!reqdata.SUB_ID)
						{
							reqdata.SUB_ID = [];
						}
						reqdata.SUB_ID[reqdata.SUB_ID.length] = obj[i].value;
					}
				}
				else if ('action_button' === obj[i].name || 'sessid' === obj[i].name)
				{
					boolSend = true;
					reqdata[obj[i].name] = obj[i].value;
				}
			}
			if (boolSend)
			{
				BX.showWait(this.LAYOUT);
				BX.ajax.post(this.list_url+'&mode=frame',reqdata,BX.delegate( this._ShowAjaxResult, this));
			}
		}
	}
};

BX.adminSubList.prototype.ExecuteFormAction = function(id)
{
	var boolSend,
		reqdata,
		obj,
		i,
		form_info,
		bAttr,
		cutname,
		j,
		multiCheck,
		actions;

	if (!!id && !!this[id] && typeof this[id] === 'object')
	{
		boolSend = false;
		reqdata = {};
		if ('ACTION_BUTTON' === id)
		{
			this.ACTION_VALUE_BUTTON.value = this.ACTION_SELECTOR[this.ACTION_SELECTOR.selectedIndex].value;
			if (this.ACTION_SELECTOR[this.ACTION_SELECTOR.selectedIndex].getAttribute('custom_action'))
			{
				eval(this.ACTION_SELECTOR[this.ACTION_SELECTOR.selectedIndex].getAttribute('custom_action'));
			}

			obj = BX.findChildren(this.FORM,{'attr': {'name' : 'SUB_ID[]'}},true);

			if (!!obj && !!obj.length && 0 < obj.length)
			{
				for (i = 0; i < obj.length; i++)
				{
					if (obj[i].checked)
					{
						if (!reqdata.SUB_ID)
						{
							reqdata.SUB_ID = [];
						}
						reqdata.SUB_ID[reqdata.SUB_ID.length] = obj[i].value;
					}
				}

				if (BX.type.isElementNode(this.FOOTER))
				{
					actions = BX.findChild(this.FOOTER, { attr: {'data-action-item' : 'Y'} }, true, true);
					if (BX.type.isArray(actions))
					{
						for (i = 0; i < actions.length; i++)
						{
							reqdata[actions[i].name] = actions[i].value;
						}
					}
					actions = null;
				}

				reqdata.action_button = this.ACTION_VALUE_BUTTON.value;
				reqdata.sessid = BX('sessid').value;
				boolSend = true;
			}
		}
		else if ('SAVE_BUTTON' === id)
		{
			form_info = BX.findChildren(this.FORM,{},true);
			if (!!form_info && !!form_info.length && 0 < form_info.length)
			{
				for (i = 0; i < form_info.length; i++)
				{
					if (!!form_info[i].name)
					{
						bAttr = true;
						if ('radio' === form_info[i].type || 'checkbox' === form_info[i].type)
						{
							if (!form_info[i].checked)
							{
								bAttr = false;
							}
						}
						else if ('file' === form_info[i].type)
						{
							bAttr = false;
						}
						if (bAttr)
						{
							if ('select-multiple' === form_info[i].type)
							{
								if (0 < form_info[i].length)
								{
									cutname = form_info[i].name.replace('[]','');
									for (j = 0; j < form_info[i].length; j++)
									{
										if (form_info[i].options[j].selected)
										{
											if (!reqdata[cutname])
											{
												reqdata[cutname] = [];
											}
											reqdata[cutname][reqdata[cutname].length] = form_info[i].options[j].value;
										}
									}
								}
							}
							else if ('checkbox' === form_info[i].type)
							{
								multiCheck = false;
								if (form_info[i].name.length > 2)
								{
									multiCheck = (form_info[i].name.substr(form_info[i].name.length-2) === '[]');
								}
								if (multiCheck)
								{
									cutname = form_info[i].name.replace('[]','');
									if (!reqdata[cutname])
									{
										reqdata[cutname] = [];
									}
									reqdata[cutname][reqdata[cutname].length] = form_info[i].value;
								}
								else
								{
									reqdata[form_info[i].name] = form_info[i].value;
								}
							}
							else
							{
								reqdata[form_info[i].name] = form_info[i].value;
							}
						}
					}
				}
				reqdata.save = 'yes';
				reqdata.sessid = BX('sessid').value;
				boolSend = true;
			}
		}

		if (boolSend)
		{
			BX.showWait(this.LAYOUT);
			BX.ajax.post(this.list_url+'&mode=frame', reqdata, BX.delegate(this._ShowAjaxResult, this));
		}
	}
};

BX.adminSubList.prototype._ShowAjaxResult = function(result)
{
	BX.closeWait(this.LAYOUT);
	this._GetAdminList(result);
};

/* overloading methods */
BX.adminSubList.prototype.Init = function()
{
	var i,
		checkboxList,
		pos,
		wndSize,
		wndScroll;

	this.TABLE = BX(this.table_id);

	this.LAYOUT = BX(this.table_id + '_result_div');
	this.FOOTER = BX(this.table_id + '_footer');
	this.FOOTER_EDIT = BX(this.table_id + '_footer_edit');
	this.FORM = BX('form_' + this.table_id);
	this.PARENT_FORM = BX.findParent(this.FORM, { tag: 'form' });

	this.CHECKBOX_COUNTER = BX(this.table_id + '_selected_count');

	this.ACTION_SELECTOR = BX(this.table_id + '_action');
	this.ACTION_VALUE_BUTTON = BX(this.table_id + '_action_button');
	this.ACTION_BUTTON = BX(this.table_id + '_apply_sub_button');
	this.ACTION_TARGET = BX(this.table_id + '_action_sub_target');
	this.SAVE_BUTTON = BX(this.table_id + '_save_sub_button');

	this.BUTTON_EDIT = BX(this.table_id + '_action_edit_button');
	this.BUTTON_DELETE = BX(this.table_id + '_action_delete_button');

	BX.bind(this.ACTION_SELECTOR, 'change', BX.proxy(this.UpdateCheckboxCounter, this));
	BX.bind(this.ACTION_TARGET, 'click', BX.proxy(this.UpdateCheckboxCounter, this));

	BX.bindDelegate(this.FOOTER, 'change', { tagName: 'select', attr: {'data-use-actions' : 'Y'} }, BX.proxy(this.CheckGroupActions, this));

	if (!!this.TABLE && this.TABLE.tBodies[0] && this.TABLE.tBodies[0].rows.length > 0)
	{
		for (i = 0; i < this.TABLE.tBodies[0].rows.length; i++)
		{
			if (this.TABLE.tBodies[0].rows[i].oncontextmenu)
			{
				BX.bind(this.TABLE.tBodies[0].rows[i], 'contextmenu', BX.proxy(function(e)
				{
					if(!this.params.context_ctrl && e.ctrlKey || this.params.context_ctrl && !e.ctrlKey)
					{
						return;
					}

					BX.adminSubList.ShowMenu({x: e.pageX || (e.clientX + document.body.scrollLeft), y: e.pageY || (e.clientY + document.body.scrollTop)}, BX.proxy_context.oncontextmenu(), BX.proxy_context);

					return BX.PreventDefault(e);

				}, this));
			}

			BX.bind(this.TABLE.tBodies[0].rows[i], 'click', BX.proxy(this.RowClick, this));
		}
	}

	checkboxList = BX.findChildren(this.LAYOUT || this.TABLE, {tagName: 'INPUT', property: {type: 'checkbox'}}, true);
	if (!!checkboxList)
	{
		for (i = 0; i < checkboxList.length; i++)
		{
			BX.adminFormTools.modifyCheckbox(checkboxList[i]);
			if(checkboxList[i].name === 'SUB_ID[]')
			{
				if (!checkboxList[i].disabled)
				{
					BX.bind(checkboxList[i], 'click', BX.proxy(this._checkboxClick, this));
					BX.bind(checkboxList[i].parentNode, 'click', BX.proxy(this._checkboxCellClick, this));
					BX.bind(checkboxList[i].parentNode, 'dblclick', BX.PreventDefault);

					this.CHECKBOX.push(checkboxList[i]);
				}
				else
				{
					this.CHECKBOX_DISABLED.push(checkboxList[i]);
				}
			}
		}
	}

	if (this.FOOTER || this.FOOTER_EDIT)
	{
		BX.adminFormTools.modifyFormElements(this.FOOTER || this.FOOTER_EDIT, ['*']);
	}

	if (!!this.LAYOUT)
	{
		pos = BX.pos(this.LAYOUT);
		wndScroll = BX.GetWindowSize();
		if (!!this.FOOTER_EDIT)
		{
			wndSize = BX.GetWindowSize();
			if (!!this.CHECKBOX_DISABLED[0])
			{
				pos = BX.pos(this.CHECKBOX_DISABLED[0].parentNode);
			}

			window.scrollTo(wndScroll.scrollLeft, pos.top - parseInt(wndScroll.innerHeight/2));
		}
		else if (pos.top < wndScroll.scrollTop)
		{
			window.scrollTo(wndScroll.scrollLeft, pos.top);
		}
	}


	this.UpdateCheckboxCounter();

	this.MAIN_BUTTON_BTNSAVE = BX('savebtn');
	this.MAIN_BUTTON_DONTSAVE = BX('dontsave');
	this.MAIN_BUTTON_SAVE = BX('save');
	this.MAIN_BUTTON_APPLY = BX('apply');
	this.MAIN_BUTTON_CANCEL = BX('cancel');
	this.MAIN_BUTTON_SAVE_ADD = BX('save_and_add');

	if (!(
		BX.type.isElementNode(this.MAIN_BUTTON_SAVE)
		&& BX.type.isElementNode(this.MAIN_BUTTON_APPLY)
		&& BX.type.isElementNode(this.MAIN_BUTTON_CANCEL)
		&& BX.type.isElementNode(this.MAIN_BUTTON_SAVE_ADD)
	))
	{
		if (BX.type.isElementNode(this.PARENT_FORM))
		{
			this.MAIN_BUTTON_SAVE = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'submit', name: 'save' }}, true, false);
			if (!BX.type.isElementNode(this.MAIN_BUTTON_SAVE))
			{
				this.MAIN_BUTTON_SAVE = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'button', name: 'save' }}, true, false);
			}
			this.MAIN_BUTTON_APPLY = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'submit', name: 'apply' }}, true, false);
			if (!BX.type.isElementNode(this.MAIN_BUTTON_APPLY))
			{
				this.MAIN_BUTTON_APPLY = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'button', name: 'apply' }}, true, false);
			}
			this.MAIN_BUTTON_CANCEL = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'button', name: 'cancel' }}, true, false);
			this.MAIN_BUTTON_SAVE_ADD = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'submit', name: 'save_and_add' }}, true, false);
			if (!BX.type.isElementNode(this.MAIN_BUTTON_SAVE_ADD))
			{
				this.MAIN_BUTTON_SAVE_ADD = BX.findChild(this.PARENT_FORM, { tag: 'input', attribute: { type: 'button', name: 'save_and_add' }}, true, false);
			}
		}
	}
};

BX.adminSubList.prototype.GetAdminList = function(url, callback)
{
	BX.showWait(this.LAYOUT);

	url = BX.util.remove_url_param(url, ['mode', 'table_id']);
	url += (url.indexOf('?') >= 0 ? '&' : '?') + 'mode=list&table_id='+BX.util.urlencode(this.table_id);

	BX.ajax({
		method: 'POST',
		dataType: 'html',
		url: url,
		onsuccess: BX.delegate(function(result) {
			if (result.length > 0)
			{
				BX.closeWait(this.LAYOUT);
				this._GetAdminList(result);
				this._ActivateMainForm();
				if (callback && BX.type.isFunction(callback))
					callback();
			}
		}, this),
		onfailure: function() {BX.debug('GetAdminList', arguments);}
	});
};

BX.adminSubList.prototype._GetAdminList = function(result)
{
	this.Destroy(false);
	this.LAYOUT.innerHTML = result;
	this.ReInit();
};

BX.adminSubList.prototype.SaveSettings =  function()
{
	BX.showWait();

	var sCols='', sBy='', sOrder='', sPageSize='',
		oSelect,
		n,
		i,
		bCommon,
		url;

	oSelect = document.list_settings.selected_columns;
	n = oSelect.length;
	for (i=0; i<n; i++)
	{
		sCols += (sCols !== '' ? ',':'')+oSelect[i].value;
	}

	oSelect = document.list_settings.order_field;
	if(oSelect)
	{
		sBy = oSelect[oSelect.selectedIndex].value;
	}

	oSelect = document.list_settings.order_direction;
	if(oSelect)
	{
		sOrder = oSelect[oSelect.selectedIndex].value;
	}

	oSelect = document.list_settings.nav_page_size;
	sPageSize = oSelect[oSelect.selectedIndex].value;

	bCommon = (document.list_settings.set_default && document.list_settings.set_default.checked);

	BX.userOptions.save('list', this.table_id, 'columns', sCols, bCommon);
	BX.userOptions.save('list', this.table_id, 'by', sBy, bCommon);
	BX.userOptions.save('list', this.table_id, 'order', sOrder, bCommon);
	BX.userOptions.save('list', this.table_id, 'page_size', sPageSize, bCommon);

	url = this.list_url;
	BX.userOptions.send(BX.delegate(function(){
		BX.closeWait();
		this.GetAdminList(
			url,
			function(){
				var wnd = top.BX.WindowManager.Get() || BX.WindowManager.Get();
				if (wnd !== null)
				{
					wnd.Close();
				}
				wnd = null;
			}
		);
	}, this));
};

BX.adminSubList.prototype.DeleteSettings = function(bCommon)
{
	BX.showWait();
	var url = this.list_url;
	BX.userOptions.del('list', this.table_id, bCommon, BX.delegate(function(){
		BX.closeWait();
		this.GetAdminList(
			url,
			function(){
				var wnd = top.BX.WindowManager.Get() || BX.WindowManager.Get();
				if (wnd !== null)
				{
					wnd.Close();
				}
				wnd = null;
			}
		);
	}, this));
};

BX.adminSubList.ShowMenu = function(el, menu, el_row)
{
	BX.adminList.ShowMenu.apply(this,[ el, menu, el_row]);
};

BX.adminSubList.prototype.CheckGroupActions = function()
{
	var target = BX.proxy_context,
		data,
		list,
		i,
		block;

	if (!target.hasAttribute('data-actions'))
		return;
	data = target.getAttribute('data-actions');
	if (!BX.type.isNotEmptyString(data))
		return;
	list = JSON.parse(data);
	for (i = 0; i < list.length; i++)
	{
		block = BX(list[i].BLOCK);
		if (BX.type.isElementNode(block))
		{
			block.style.display = (target.value === list[i].VALUE ? 'inline-block' : 'none');
		}
		block = null;
	}
	list = null;
	data = null;
	target = null;
};
})(window);