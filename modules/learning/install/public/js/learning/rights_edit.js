function LearningJSRightsAccess(id, arSelected, variable_name, table_id, href_id, sSelect, arHighLight)
{
	this.id = id;
	this.arSelected = arSelected;
	this.variable_name = variable_name;
	this.table_id = table_id;
	this.href_id = href_id;
	this.sSelect = sSelect;
	this.arHighLight = arHighLight;

	BX.ready(BX.delegate(this.Init, this));
}

LearningJSRightsAccess.prototype.Init = function()
{
	BX.bind(BX(this.href_id), 'click', BX.delegate(this.Add, this));
	var heading = BX(this.variable_name + '_heading');
	if(heading)
		BX.bind(heading, 'dblclick', BX.delegate(this.ShowInfo, this));
	BX.Access.Init(this.arHighLight);
	BX.Access.SetSelected(this.arSelected, this.variable_name);
}

LearningJSRightsAccess.prototype.Add = function()
{
	BX.Access.ShowForm({callback: BX.delegate(this.InsertRights, this), bind: this.variable_name})
}

LearningJSRightsAccess.prototype.InsertRights = function(obSelected)
{
	var tbl = BX(this.table_id);
	for(var provider in obSelected)
	{
		for(var id in obSelected[provider])
		{
			var providerName = BX.Access.GetProviderName(provider);
			var cnt = tbl.rows.length;
			var row = tbl.insertRow(cnt-1);
			row.style.marginTop = '5px;'
			row.vAlign = 'top';
			row.insertCell(-1);
			row.insertCell(-1);
			row.cells[0].align = 'right';

			var providerString = '';

			if (providerName.length > 0)
			{
				providerString = providerName + ' ';
			}

			providerString = providerString + BX.Text.encode(obSelected[provider][id].name);

			row.cells[0].innerHTML = '<div style="padding-top:8px;"><span class="access-delete" style="position:relative; top:1px; margin-right:3px;"  onclick="LearningJSRightsAccess.DeleteRow(this, \''+id+'\', \''+this.variable_name+'\')">&nbsp;</span>'
				+ providerString + ':&nbsp;' + '<input type="hidden" name="' + this.variable_name + '[][GROUP_CODE]" value="' + id + '"></div>';

			row.cells[1].align = 'left';
			row.cells[1].innerHTML = '<div style="padding-top: 8px; min-width:720px;"><span title="'+BX.message('langApplyTitle')+'" id="overwrite_'+id+'"></span>' + this.sSelect + '</div>';

			var parents = BX.findChildren(tbl, {'class' : this.variable_name + '_row_for_' + id}, true);
			if(parents)
			for(var i = 0; i < parents.length; i++)
				parents[i].className += ' iblock-strike-out';
		}
	}
}

LearningJSRightsAccess.DeleteRow = function(ob, id, variable_name)
{
	var row = BX.findParent(ob, {'tag':'tr'});
	var tbl = BX.findParent(row, {'tag':'table'});
	var parents = BX.findChildren(tbl, {'class' : variable_name + '_row_for_' + id + ' iblock-strike-out'}, true);
	if(parents)
	for(var i = 0; i < parents.length; i++)
		parents[i].className = variable_name + '_row_for_' + id;
	row.parentNode.removeChild(row);
	BX.Access.DeleteSelected(id, variable_name);
}

function addNewRow(tableID, row_to_clone)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	if(row_to_clone == null)
		row_to_clone = -2;
	var sHTML = tbl.rows[cnt+row_to_clone].cells[0].innerHTML;
	var oRow = tbl.insertRow(cnt+row_to_clone+1);
	var oCell = oRow.insertCell(0);

	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('[n',p);
		if(s<0)break;
		var e = sHTML.indexOf(']',s);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+2,e-s));
		sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
		p=s+1;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__n',p);
		if(s<0)break;
		var e = sHTML.indexOf('_',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__n'+(++n)+'_'+sHTML.substr(e+1);
		p=e+1;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__N',p);
		if(s<0)break;
		var e = sHTML.indexOf('__',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__N'+(++n)+'__'+sHTML.substr(e+2);
		p=e+2;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('xxn',p);
		if(s<0)break;
		var e = sHTML.indexOf('xx',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'xxn'+(++n)+'xx'+sHTML.substr(e+2);
		p=e+2;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('%5Bn',p);
		if(s<0)break;
		var e = sHTML.indexOf('%5D',s+3);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+4,e-s));
		sHTML = sHTML.substr(0, s)+'%5Bn'+(++n)+'%5D'+sHTML.substr(e+3);
		p=e+3;
	}
	oCell.innerHTML = sHTML;

	var patt = new RegExp ("<"+"script"+">[^\000]*?<"+"\/"+"script"+">", "ig");
	var code = sHTML.match(patt);
	if(code)
	{
		for(var i = 0; i < code.length; i++)
		{
			if(code[i] != '')
			{
				var s = code[i].substring(8, code[i].length-9);
				jsUtils.EvalGlobal(s);
			}
		}
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
