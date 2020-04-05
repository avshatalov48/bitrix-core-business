/*
 * arParams
 *		PREFIX - prefix for vars
 *		FORM_ID - id form
 *		TABLE_PROP_ID - id table with properties
 *		PROP_COUNT_ID - id field with count properties
 *		IBLOCK_ID - id iblock
 *		LANG - lang id
 *		TITLE - window title
 *		OBJ - object var name
 * Variables
 *		this.PREFIX
 *		this.PREFIX_TR
 *		this.FORM_ID
 *		this.FORM_DATA
 *		this.TABLE_PROP_ID
 *		this.PROP_TBL
 *		this.PROP_COUNT_ID
 *		this.PROP_COUNT
 *		this.PROP_COUNT_VALUE
 *		this.IBLOCK_ID
 *		this.LANG
 *		this.TITLE
 *		this.CELLS
 *		this.CELL_IND
 *		this.CELL_CENT
 *		this.OBJNAME
 */
function JCIBlockProperty(arParams)
{
	if (!arParams)
	{
		return;
	}

	this.intERROR = 0;
	this.PREFIX = arParams.PREFIX;
	this.PREFIX_TR = this.PREFIX+'ROW_';
	this.FORM_ID = arParams.FORM_ID;
	this.TABLE_PROP_ID = arParams.TABLE_PROP_ID;
	this.PROP_COUNT_ID = arParams.PROP_COUNT_ID;
	this.IBLOCK_ID = arParams.IBLOCK_ID;
	this.LANG = arParams.LANG;
	this.TITLE = arParams.TITLE;
	this.CELLS = [];
	this.CELL_IND = -1;
	this.CELL_CENT = [];
	this.OBJNAME = arParams.OBJ;

	BX.ready(BX.delegate(this.Init,this));
}

JCIBlockProperty.prototype.Init = function()
{
	var clButtons = null,
		i = 0;

	this.FORM_DATA = BX(this.FORM_ID);
	if (!this.FORM_DATA)
	{
		this.intERROR = -1;
		return;
	}
	this.PROP_TBL = BX(this.TABLE_PROP_ID);
	if (!this.PROP_TBL)
	{
		this.intERROR = -1;
		return;
	}
	this.PROP_COUNT = BX(this.PROP_COUNT_ID);
	if (!this.PROP_COUNT)
	{
		this.intERROR = -1;
		return;
	}
	clButtons = BX.findChildren(this.PROP_TBL, {'tag': 'input','attribute': { 'type':'button'}}, true);
	if (!!clButtons)
	{
		for (i = 0; i < clButtons.length; i++)
		{
			BX.bind(clButtons[i], 'click', BX.proxy(this.ShowPropertyDialog, this));
		}
	}

	BX.addCustomEvent(this.FORM_DATA, 'onAutoSaveRestore', BX.delegate(this.onAutoSaveRestore, this));
};

JCIBlockProperty.prototype.GetPropInfo = function(ID)
{
	if (0 > this.intERROR)
		return {};

	ID = this.PREFIX + ID;

	return {
		'PROPERTY_TYPE' : this.FORM_DATA[ID+'_PROPERTY_TYPE'].value,
		'NAME' : this.FORM_DATA[ID+'_NAME'].value,
		'ACTIVE' : (this.FORM_DATA[ID+'_ACTIVE_Y'].checked ? this.FORM_DATA[ID+'_ACTIVE_Y'].value : this.FORM_DATA[ID+'_ACTIVE_N'].value),
		'MULTIPLE' : (this.FORM_DATA[ID+'_MULTIPLE_Y'].checked ? this.FORM_DATA[ID+'_MULTIPLE_Y'].value : this.FORM_DATA[ID+'_MULTIPLE_N'].value),
		'IS_REQUIRED' : (this.FORM_DATA[ID+'_IS_REQUIRED_Y'].checked ? this.FORM_DATA[ID+'_IS_REQUIRED_Y'].value : this.FORM_DATA[ID+'_IS_REQUIRED_N'].value),
		'SORT' : this.FORM_DATA[ID+'_SORT'].value,
		'CODE' : this.FORM_DATA[ID+'_CODE'].value,
		'PROPINFO': this.FORM_DATA[ID+'_PROPINFO'].value
	};
};

JCIBlockProperty.prototype.SetPropInfo = function(ID,arProp,formsess)
{
	var i = 0,
		PropActive = null,
		PropMulti = null,
		PropReq = null;

	if (0 > this.intERROR)
	{
		return;
	}

	if (!formsess)
	{
		return;
	}
	if (BX.bitrix_sessid() !== formsess)
	{
		return;
	}

	ID = this.PREFIX+ID;

	this.FORM_DATA[ID+'_NAME'].value = arProp.NAME;
	this.FORM_DATA[ID+'_SORT'].value = arProp.SORT;
	this.FORM_DATA[ID+'_CODE'].value = arProp.CODE;
	PropActive = BX(ID+'_ACTIVE_Y');
	PropActive.checked = ('Y' === arProp.ACTIVE);
	PropMulti = BX(ID+'_MULTIPLE_Y');
	PropMulti.checked = ('Y' === arProp.MULTIPLE);
	PropReq = BX(ID+'_IS_REQUIRED_Y');
	PropReq.checked = ('Y' === arProp.IS_REQUIRED);
	this.FORM_DATA[ID+'_PROPINFO'].value = arProp.PROPINFO;
	for (i = 0; i < this.FORM_DATA[ID+'_PROPERTY_TYPE'].length; i++)
	{
		if (arProp.PROPERTY_TYPE === this.FORM_DATA[ID+'_PROPERTY_TYPE'].options[i].value)
		{
			this.FORM_DATA[ID+'_PROPERTY_TYPE'].options[i].selected = true;
		}
	}

	BX.fireEvent(this.FORM_DATA[ID+'_NAME'], 'change');
};

JCIBlockProperty.prototype.GetProperty = function(strName)
{
	if (0 > this.intERROR)
		return '';

	if (!strName || !this[strName])
		return '';

	return this[strName];
};

JCIBlockProperty.prototype.SetProperty = function(strName,value)
{
	if (0 > this.intERROR)
	{
		return;
	}

	if (strName)
	{
		this[strName] = value;
	}
};

JCIBlockProperty.prototype.ShowPropertyDialog = function ()
{
	if (0 > this.intERROR)
	{
		return;
	}
	var target = BX.proxy_context,
		ID = '',
		arResult = {};

	if (!!target && target.hasAttribute('data-propid'))
	{
		ID = target.getAttribute('data-propid');

		arResult = {
			'PARAMS': {
				'PREFIX': this.PREFIX,
				'ID': ID,
				'IBLOCK_ID': this.IBLOCK_ID,
				'TITLE': this.TITLE,
				'RECEIVER': this.OBJNAME
			},
			'PROP': this.GetPropInfo(ID),
			'sessid': BX.bitrix_sessid()
		};
		(new BX.CAdminDialog({
			'title': this.TITLE,
			'content_url': '/bitrix/admin/iblock_edit_property.php?lang='+this.LANG+'&propedit='+ID+'&bxpublic=Y&receiver='+this.OBJNAME,
			'content_post': arResult,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
};

JCIBlockProperty.prototype.SetCells = function(arCells,intIndex,arCenter)
{
	var i = 0;

	if (0 > this.intERROR)
	{
		return;
	}

	if (arCells)
	{
		this.CELLS = BX.clone(arCells,true);
	}
	for (i = 0; i < this.CELLS.length; i++)
	{
		this.CELLS[i] = this.CELLS[i].replace(/PREFIX/ig, this.PREFIX);
	}
	if (intIndex)
	{
		this.CELL_IND = intIndex;
	}
	if (arCenter)
	{
		this.CELL_CENT = BX.clone(arCenter,true);
	}
};

JCIBlockProperty.prototype.addPropRow = function()
{
	if (0 > this.intERROR)
	{
		return;
	}
	var i = 0,
		id = parseInt(this.PROP_COUNT.value, 10),
		needCell = '',
		newRow = null,
		oCell = null,
		typeHtml = '',
		clButtons = null;

	newRow = this.PROP_TBL.insertRow(this.PROP_TBL.rows.length);
	newRow.id = this.PREFIX_TR+'n'+id;
	for (i = 0; i < this.CELLS.length; i++)
	{
		oCell = newRow.insertCell(-1);
		typeHtml = this.CELLS[i];
		typeHtml = typeHtml.replace(/tmp_xxx/ig, 'n'+id);
		oCell.innerHTML = typeHtml;
	}
	for (i = 0; i < this.CELL_CENT.length; i++)
	{
		needCell = newRow.cells[this.CELL_CENT[i]-1];
		if (!!needCell)
		{
			BX.adjust(needCell, { style: {'textAlign': 'center', 'verticalAlign' : 'middle'} });
		}
	}

	needCell = newRow.cells[0];
	if (!!needCell)
	{
		BX.adjust(needCell, { style: {'verticalAlign' : 'middle'} });
	}

	if (newRow.cells[this.CELL_IND])
	{
		needCell = newRow.cells[this.CELL_IND];
		clButtons = BX.findChildren(needCell, {'tag': 'input','attribute': { 'type':'button'}}, true);
		if (!!clButtons)
		{
			for (i = 0; i < clButtons.length; i++)
			{
				BX.bind(clButtons[i], 'click', BX.proxy(this.ShowPropertyDialog, this));
			}
		}
	}

	BX.adminFormTools.modifyFormElements(this.FORM_ID);

	setTimeout(function() {
		var i = 0,
			l = 0,
			r = BX.findChildren(newRow.parentNode, {tag: /^(input|select|textarea)$/i}, true);
		if (r && r.length > 0)
		{
			for (i=0, l = r.length;i<l;i++)
			{
				if (r[i].form && r[i].form.BXAUTOSAVE)
				{
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				}
				else
				{
					break;
				}
			}
		}
	}, 10);

	this.PROP_COUNT.value = id + 1;
};

JCIBlockProperty.prototype.onAutoSaveRestore = function(ob, data)
{
	while (data['IB_PROPERTY_n' + this.PROP_COUNT.value + '_NAME'])
	{
		this.addPropRow();
	}
};

function JCIBlockAccess(entity_type, iblock_id, id, arSelected, variable_name, table_id, href_id, sSelect, arHighLight)
{
	this.entity_type = entity_type;
	this.iblock_id = iblock_id;
	this.id = id;
	this.arSelected = arSelected;
	this.variable_name = variable_name;
	this.table_id = table_id;
	this.href_id = href_id;
	this.sSelect = sSelect;
	this.arHighLight = arHighLight;

	BX.ready(BX.delegate(this.Init, this));
}

JCIBlockAccess.prototype.Init = function()
{
	BX.bind(BX(this.href_id), 'click', BX.delegate(this.Add, this));
	var heading = BX(this.variable_name + '_heading');
	if(heading)
	{
		BX.bind(heading, 'dblclick', BX.delegate(this.ShowInfo, this));
	}
	BX.Access.Init(this.arHighLight);
	BX.Access.SetSelected(this.arSelected, this.variable_name);
};

JCIBlockAccess.prototype.Add = function()
{
	BX.Access.ShowForm({callback: BX.delegate(this.InsertRights, this), bind: this.variable_name})
};

JCIBlockAccess.prototype.InsertRights = function(obSelected)
{
	var tbl = BX(this.table_id);
	for(var provider in obSelected)
	{
		if (obSelected.hasOwnProperty(provider))
		{
			for(var id in obSelected[provider])
			{
				if (obSelected[provider].hasOwnProperty(id))
				{
					var cnt = tbl.rows.length;
					var row = tbl.insertRow(cnt-1);
					row.vAlign = 'top';
					row.insertCell(-1);
					row.insertCell(-1);
					row.cells[0].align = 'right';
					row.cells[0].style.textAlign = 'right';
					row.cells[0].style.verticalAlign = 'middle';
					row.cells[0].innerHTML = BX.Access.GetProviderName(provider)+' '+obSelected[provider][id].name+':'+'<input type="hidden" name="'+this.variable_name+'[][RIGHT_ID]" value=""><input type="hidden" name="'+this.variable_name+'[][GROUP_CODE]" value="'+id+'">';
					row.cells[1].align = 'left';
					row.cells[1].innerHTML = this.sSelect + ' ' + '<a href="javascript:void(0);" onclick="JCIBlockAccess.DeleteRow(this, \''+id+'\', \''+this.variable_name+'\')" class="access-delete"></a><span title="'+BX.message('langApplyTitle')+'" id="overwrite_'+id+'"></span>';

					var parents = BX.findChildren(tbl, {'class' : this.variable_name + '_row_for_' + id}, true);
					if(parents)
					for(var i = 0; i < parents.length; i++)
						parents[i].className += ' iblock-strike-out';
				}
			}
		}
	}

	if(parseInt(this.id) > 0)
	{
		BX.ajax.loadJSON(
			'/bitrix/admin/iblock_edit.php'+
			'?ajax=y'+
			'&sessid='+BX.bitrix_sessid()+
			'&entity_type='+this.entity_type+
			'&iblock_id='+this.iblock_id+
			'&id='+this.id,
			{added: obSelected},
			function(result)
			{
				if(result)
				{
					for(var id in result)
					{
						var s = parseInt(result[id][0]);
						var e = parseInt(result[id][1]);
						var mess = '';
						if(s > 0 && e > 0)
							mess = BX.message('langApply1Title');
						else if (s > 0)
							mess = BX.message('langApply2Title');
						else if (e > 0)
							mess = BX.message('langApply3Title');

						if(mess)
							BX('overwrite_'+id).innerHTML = '<br><input type="checkbox" name="'+this.variable_name+'[][DO_CLEAN]" value="Y" checked="checked" disabled="disabled">'+mess+' ('+(s+e)+')';
					}
				}
			}
		);
	}

	BX.onCustomEvent('onAdminTabsChange');
};

JCIBlockAccess.prototype.ShowInfo = function()
{
	var entity_type = this.entity_type;
	var iblock_id = this.iblock_id;
	var id = this.id;

	var btnOK = new BX.CWindowButton({
		'title': 'Query',
		'action': function()
		{
			var _user_id = BX('prompt_user_id');
			BX('info_result').innerHTML = '';
			BX.showWait();
			BX.ajax.loadJSON(
				'/bitrix/admin/iblock_edit.php'+
				'?ajax=y'+
				'&sessid='+BX.bitrix_sessid()+
				'&entity_type='+entity_type+
				'&iblock_id='+iblock_id+
				'&id='+id,
				{info: _user_id.value},
				function(result)
				{
					if(result)
					{
						for(var id in result)
						{
							BX('info_result').innerHTML += '<span style="display:inline-block;width:200px;height:15px;">' + id + '</span>';
						}
					}
					BX.closeWait();
				}
			);
		}
	})

	if (null == this.iblock_info_obDialog)
	{
		this.iblock_info_obDialog = new BX.CDialog({
			content: '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td width="50%" align="right">User ID:</td><td width="50%" align="left"><input type="text" size="6" id="prompt_user_id" value=""></td></tr><tr><td colspan="2" id="info_result"></td></tr></table>',
			buttons: [btnOK, BX.CDialog.btnCancel],
			width: 420,
			height: 200
		});
	}

	this.iblock_info_obDialog.Show();

	var inp = BX('prompt_user_id');
	inp.focus();
	inp.select();
};

JCIBlockAccess.DeleteRow = function(ob, id, variable_name)
{
	var row = BX.findParent(ob, {'tag':'tr'});
	var tbl = BX.findParent(row, {'tag':'table'});
	var parents = BX.findChildren(tbl, {'class' : variable_name + '_row_for_' + id + ' iblock-strike-out'}, true);
	if(parents)
	for(var i = 0; i < parents.length; i++)
		parents[i].className = variable_name + '_row_for_' + id;
	row.parentNode.removeChild(row);
	BX.onCustomEvent('onAdminTabsChange');
	BX.Access.DeleteSelected(id, variable_name);
};

function addNewRow(tableID, row_to_clone)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	if(row_to_clone == null)
		row_to_clone = -2;
	var sHTML = tbl.rows[cnt+row_to_clone].cells[0].innerHTML;
	var oRow = tbl.insertRow(cnt+row_to_clone+1);
	var oCell = oRow.insertCell(0);

	var s, e, n, p;
	p = 0;
	while(true)
	{
		s = sHTML.indexOf('[n',p);
		if(s<0)break;
		e = sHTML.indexOf(']',s);
		if(e<0)break;
		n = parseInt(sHTML.substr(s+2,e-s));
		sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
		p=s+1;
	}
	p = 0;
	while(true)
	{
		s = sHTML.indexOf('__n',p);
		if(s<0)break;
		e = sHTML.indexOf('_',s+2);
		if(e<0)break;
		n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__n'+(++n)+'_'+sHTML.substr(e+1);
		p=e+1;
	}
	p = 0;
	while(true)
	{
		s = sHTML.indexOf('__N',p);
		if(s<0)break;
		e = sHTML.indexOf('__',s+2);
		if(e<0)break;
		n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__N'+(++n)+'__'+sHTML.substr(e+2);
		p=e+2;
	}
	p = 0;
	while(true)
	{
		s = sHTML.indexOf('xxn',p);
		if(s<0)break;
		e = sHTML.indexOf('xx',s+2);
		if(e<0)break;
		n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'xxn'+(++n)+'xx'+sHTML.substr(e+2);
		p=e+2;
	}
	p = 0;
	while(true)
	{
		s = sHTML.indexOf('%5Bn',p);
		if(s<0)break;
		e = sHTML.indexOf('%5D',s+3);
		if(e<0)break;
		n = parseInt(sHTML.substr(s+4,e-s));
		sHTML = sHTML.substr(0, s)+'%5Bn'+(++n)+'%5D'+sHTML.substr(e+3);
		p=e+3;
	}

	var htmlObject = {'html': sHTML};
	BX.onCustomEvent(window, 'onAddNewRowBeforeInner', [htmlObject]);
	sHTML = htmlObject.html;

	oCell.innerHTML = sHTML;

	var patt = new RegExp ("<"+"script"+">[^\000]*?<"+"\/"+"script"+">", "ig");
	var code = sHTML.match(patt);
	if(code)
	{
		for(var i = 0; i < code.length; i++)
		{
			if(code[i] != '')
			{
				s = code[i].substring(8, code[i].length-9);
				jsUtils.EvalGlobal(s);
			}
		}
	}

	if (BX && BX.adminPanel)
	{
		BX.adminPanel.modifyFormElements(oRow);
		BX.onCustomEvent('onAdminTabsChange');
	}

	setTimeout(function() {
		var r = BX.findChildren(oCell, {tag: /^(input|select|textarea)$/i});
		if (r && r.length > 0)
		{
			for (var i=0,l=r.length;i<l;i++)
			{
				if (r[i].form && r[i].form.BXAUTOSAVE)
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				else
					break;
			}
		}
	}, 10);
}

function JCIBlockGroupField(form, groupSection_id, ajaxURL)
{
	this.form = form;
	this.groupSection = BX(groupSection_id);
	this.ajaxURL = ajaxURL;
}

JCIBlockGroupField.prototype.reload = function()
{
	if (!window.JCIBlockGroupFieldIsRunning)
	{
		window.JCIBlockGroupFieldIsRunning = true;
		this.preparePost();
	}
	else
	{
		if (window.JCIBlockGroupFieldTimer)
			clearTimeout(window.JCIBlockGroupFieldTimer);
		window.JCIBlockGroupFieldTimer = setTimeout(BX.proxy(this.reload, this), 500);
	}
};

JCIBlockGroupField.prototype.preparePost = function()
{
	var i;
	var values = [];
	values[values.length] = {name : 'ajax_action', value : 'section_property'};
	values[values.length] = {name : 'sessid', value : BX.bitrix_sessid()};
	this.gatherInputsValues(values, document.getElementsByName('IBLOCK_SECTION[]'));

	var toReload = BX.findChildren(this.form, {'tag' : 'tr', 'class' : 'bx-in-group'}, true);
	if(toReload)
	{
		for(i = 0; i < toReload.length; i++)
			this.gatherInputsValues(values, BX.findChildren(toReload[i], null, true));
	}

	var formHiddens = BX.findChildren(this.form, {'tag' : 'span', 'class' : 'bx-fields-hidden'}, true);
	if(formHiddens)
	{
		for(i = 0; i < formHiddens.length; i++)
			this.gatherInputsValues(values, BX.findChildren(formHiddens[i], null, true));
	}

	BX.ajax.post(
		this.ajaxURL,
		this.values2post(values),
		BX.delegate(this.postHandler, this)
	);
};

JCIBlockGroupField.prototype.postHandler = function (result)
{
	var i;
	if(this.form)
	{
		var toDelete = BX.findChildren(this.form, {'tag' : 'tr', 'class' : 'bx-in-group'}, true);
		if(toDelete)
		{
			for(i = 0; i < toDelete.length; i++)
				this.groupSection.parentNode.removeChild(toDelete[i]);
		}

		var responseDOM = document.createElement('DIV');
		responseDOM.innerHTML = result;

		var toInsert = BX.findChildren(responseDOM, {'tag' : 'tr', 'class' : 'bx-in-group'}, true);
		if(toInsert)
		{
			var sibling = this.groupSection.nextSibling;
			for(i = 0; i < toInsert.length; i++)
			{
				var toMove = toInsert[i];
				toMove.parentNode.removeChild(toMove);
				this.groupSection.parentNode.insertBefore(toMove, sibling);
			}
		}

		var formHiddens;
		formHiddens = BX.findChildren(this.form, {'tag' : 'span', 'class' : 'bx-fields-hidden'}, true);
		if(formHiddens)
			for(i = 0; i < formHiddens.length; i++)
				formHiddens[i].parentNode.removeChild(formHiddens[i]);

		formHiddens = BX.findChildren(responseDOM, {'tag' : 'span', 'class' : 'bx-fields-hidden'}, true);
		if(formHiddens)
		{
			for(i = 0; i < formHiddens.length; i++)
			{
				var span = formHiddens[i];
				span.parentNode.removeChild(span);
				this.form.appendChild(span);
			}
		}

		BX.onCustomEvent('onAdminTabsChange');
		BX.adminPanel.modifyFormElements(this.form);
	}
	window.JCIBlockGroupFieldIsRunning = false;
};

JCIBlockGroupField.prototype.gatherInputsValues = function (values, elements)
{
	if(elements)
	{
		for(var i = 0; i < elements.length; i++)
		{
			var el = elements[i];
			if (el.disabled || !el.type)
				continue;

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'hidden':
				case 'select-one':
					values[values.length] = {name : el.name, value : el.value};
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						values[values.length] = {name : el.name, value : el.value};
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++)
					{
						if (el.options[j].selected)
							values[values.length] = {name : el.name, value : el.options[j].value};
					}
					break;
				default:
					break;
			}
		}
	}
};

JCIBlockGroupField.prototype.values2post = function (values)
{
	var post = [];
	var current = post;
	var i = 0;
	while(i < values.length)
	{
		var p = values[i].name.indexOf('[');
		if(p == -1)
		{
			current[values[i].name] = values[i].value;
			current = post;
			i++;
		}
		else
		{
			var name = values[i].name.substring(0, p);
			var rest = values[i].name.substring(p+1);
			if(!current[name])
				current[name] = [];

			var pp = rest.indexOf(']');
			if(pp == -1)
			{
				//Error - not balanced brackets
				current = post;
				i++;
			}
			else if(pp == 0)
			{
				//No index specified - so take the next integer
				current = current[name];
				values[i].name = '' + current.length;
			}
			else
			{
				//Now index name becomes and name and we go deeper into the array
				current = current[name];
				values[i].name = rest.substring(0, pp) + rest.substring(pp+1);
			}
		}
	}
	return post;
};

window.ipropTemplates = [];

function JCInheritedPropertiesTemplates(form, ajaxURL)
{
	this.form = form;
	this.ajaxURL = ajaxURL;
}

JCInheritedPropertiesTemplates.prototype.updateInheritedPropertiesTemplates = function(start)
{
	for (var i = 0; i < ipropTemplates.length; i++)
	{
		var obj_ta = BX(ipropTemplates[i].INPUT_ID);
		if (obj_ta && obj_ta.type.toLowerCase() == "textarea")
		{
			if (obj_ta.scrollHeight > obj_ta.clientHeight)
			{
				var dy = obj_ta.offsetHeight - obj_ta.clientHeight;
				var newHeight = obj_ta.scrollHeight + dy;
				obj_ta.style.height = newHeight + 'px';
			}

			var ck = BX('ck_' + ipropTemplates[i].INPUT_ID);
			if (ck)
			{
				if (ck.checked)
				{
					obj_ta.readOnly = false;
					BX('mnu_' + ipropTemplates[i].INPUT_ID).disabled = false;
				}
				else
				{
					obj_ta.readOnly = true;
					BX('mnu_' + ipropTemplates[i].INPUT_ID).disabled = true;
				}
			}
		}
	}
	if (start)
		setTimeout(function(){InheritedPropertiesTemplates.updateInheritedPropertiesValues(true)}, 100);
};

JCInheritedPropertiesTemplates.prototype.updateInheritedPropertiesValues = function(startup, force)
{
	var i, space, input, values, f, k, obj_ta, clearValues;

	if (startup)
	{
		for (i = 0; i < ipropTemplates.length; i++)
		{
			space = BX('space_' + ipropTemplates[i].ID);
			if (space)
				ipropTemplates[i].SPACE = space.value;
		}
	}

	for (i = 0; i < ipropTemplates.length; i++)
	{
		input = BX(ipropTemplates[i].INPUT_ID);
		if (!input)
			return;

		space = BX('space_' + ipropTemplates[i].ID);
		if (space)
			this.asciiOnly(space);

		if (
			force
			|| ipropTemplates[i].TEMPLATE != BX(ipropTemplates[i].INPUT_ID).value
			|| (
				space
				&& ipropTemplates[i].SPACE != space.value
			)
		)
		{
			values = [];
			f = new JCIBlockGroupField(BX(this.form));
			f.gatherInputsValues(values, BX.findChildren(BX(this.form), null, true));
			for (k = 0; k < ipropTemplates.length; k++)
			{
				obj_ta = BX(ipropTemplates[k].INPUT_ID);
				if (obj_ta && obj_ta.readOnly)
				{
					values[values.length] = {name : obj_ta.name, value : obj_ta.value}
				}
			}
			//p = f.values2post(values);
			BX.ajax.post(
				this.ajaxURL,
				f.values2post(values),
				function(data)
				{
					var DATA = [], data_test, j, k, div;
					if (BX.type.isNotEmptyString(data))
					{
						data_test = BX.parseJSON(data);
						if (data_test)
						{
							eval('DATA = ' + data);
						}
					}
					for (j = 0; j < DATA.length; j++)
					{
						if (DATA[j].htmlId)
						{
							if (BX(DATA[j].htmlId))
								BX(DATA[j].htmlId).innerHTML = DATA[j].value;
							else if (typeof  DATA[j].hiddenId != "undefined" && BX(DATA[j].hiddenId))
								BX(DATA[j].hiddenId).value = DATA[j].hiddenValue;
						}
						else
						{
							for (k = 0; k < ipropTemplates.length; k++)
							{
								if (ipropTemplates[k].ID == DATA[j].id)
								{
									div = BX(ipropTemplates[k].RESULT_ID);
									if (div)
										div.innerHTML = DATA[j].value;
									break;
								}
							}
						}
					}
				}
			);
			if (!startup)
			{
				clearValues = BX('IPROPERTY_CLEAR_VALUES');
				if (clearValues)
				{
					clearValues.value = "Y";
					if (clearValues.type.toLowerCase() == 'checkbox')
						clearValues.checked = true;
				}
			}
			this.updateInheritedPropertiesTemplates();
			break;
		}
	}

	for (i = 0; i < ipropTemplates.length; i++)
	{
		obj_ta = BX(ipropTemplates[i].INPUT_ID);
		if (obj_ta)
		{
			ipropTemplates[i].TEMPLATE = obj_ta.value;

			space = BX('space_' + ipropTemplates[i].ID);
			if (space)
			{
				ipropTemplates[i].SPACE = space.value;
			}
		}
	}

	setTimeout(function(){InheritedPropertiesTemplates.updateInheritedPropertiesValues()}, 1000);
};

JCInheritedPropertiesTemplates.prototype.insertIntoInheritedPropertiesTemplate = function(text, mnu_id, el_id)
{
	var el = BX(el_id);
	el.focus();

	var val = el.value, endIndex, range;
	if (typeof el.selectionStart != "undefined" && typeof el.selectionEnd != "undefined") {
		endIndex = el.selectionEnd;
		el.value = val.slice(0, el.selectionStart) + text + val.slice(endIndex);
		el.selectionStart = el.selectionEnd = endIndex + text.length;
	} else if (typeof document.selection != "undefined" && typeof document.selection.createRange != "undefined") {
		el.focus();
		range = document.selection.createRange();
		range.collapse(false);
		range.text = text;
		range.select();
	}

	this.updateInheritedPropertiesTemplates();
	BX.fireEvent(el, 'change');
	el.focus();
};

JCInheritedPropertiesTemplates.prototype.onTabSelect = function()
{
	this.updateInheritedPropertiesValues();
	this.updateInheritedPropertiesTemplates();
};

JCInheritedPropertiesTemplates.prototype.enableTextArea = function(el_id)
{
	var el = BX(el_id);
	var ck = BX('ck_' + el_id);
	if (el && el.readOnly)
	{
		el.readOnly = false;
		if (ck && !ck.checked)
		{
			ck.checked = true;
			this.updateInheritedPropertiesTemplates();
		}
	}
};

JCInheritedPropertiesTemplates.prototype.asciiOnly = function(el)
{
	if (el.value.length > 0)
	{
		if (el.value.length > 1)
		{
			el.value = el.value.charAt(0);
		}
		if (el.value.charCodeAt(0) > 127)
		{
			el.value = '';
		}
	}
};

function JCPopupEditor(width, height)
{
	this.width = width;
	this.height = height;
	this.popup_editor_dialog = null;
	this.input = null;
}

JCPopupEditor.prototype.openEditor = function (hiddenId, maxLength)
{
	if (!this.popup_editor_dialog)
	{
		this.popup_editor_dialog = new BX.CDialog({
			content: '<div width="100%" id="popup_editor_container"></div>',
			buttons: this.getButtons(),
			width: this.width,
			height: this.height
		});
		var popup_editor_container = BX('popup_editor_container');
		var popup_editor_start   = BX('popup_editor_start');
		popup_editor_container.parentNode.appendChild(popup_editor_start);
		popup_editor_container.parentNode.removeChild(popup_editor_container);
		popup_editor_start.style.display = '';
		LoadLHE_popup_editor_id();
	}
	this.popup_editor_dialog.Show();
	this.input = BX(hiddenId);
	popup_editor.SetEditorContent(this.input.value);
	popup_editor.SetFocus();
	this.startCharCounter();
}

JCPopupEditor.prototype.getButtons = function ()
{
	var _this = this;
	var btnOK = new BX.CWindowButton({
		title: BX.message('JS_CORE_WINDOW_SAVE'),
		id: 'savebtn',
		name: 'savebtn',
		className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
		action: function()
		{
			_this.stopCharCounter();
			this.parentWindow.Hide();
			popup_editor.SetView('html');
			_this.input.value = popup_editor.GetEditorContent();
			_this.input.onchange();
		}
	});
	var btnClose = new BX.CWindowButton({
		title: BX.message('JS_CORE_WINDOW_CLOSE'),
		id: 'closebtn',
		name: 'closebtn',
		action: function () {
			_this.stopCharCounter();
			//this.parentWindow.Close();
			this.parentWindow.Hide();
		}
	});
	return [btnOK, btnClose];
}

JCPopupEditor.prototype.startCharCounter = function()
{
	if (!this.charCounterContainer)
	{
		this.charCounterContainer = BX.create('SPAN');
		this.charCounterContainer.style.display = 'inline';
		this.popup_editor_dialog.PARTS.BUTTONS_CONTAINER.appendChild(this.charCounterContainer);
	}

	if (!this.charCounterTimer)
	{
		this.charCounterTimer = setInterval(BX.delegate(function(){
			this.updateCharCounter();
		}, this), 500);
	}
};

JCPopupEditor.prototype.updateCharCounter = function()
{
	var len = popup_editor.GetEditorContent().length;
	this.charCounterContainer.innerHTML = len;
	if (len > 255 && !this.charCounterContainer.style.color)
		this.charCounterContainer.style.color = 'red';
	if (len <= 255 && this.charCounterContainer.style.color)
		this.charCounterContainer.style.color = '';
};

JCPopupEditor.prototype.stopCharCounter = function()
{
	if (this.charCounterTimer)
		clearInterval(this.charCounterTimer);
	this.charCounterTimer = null;
};
