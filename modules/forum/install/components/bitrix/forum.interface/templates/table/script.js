function ForumTable(namespace, params)
{
	t = this;
	this.namespace = namespace;
	this.params = (!params ? {"init_checkbox" : "Y"} : {});
	
	this.checkbox = {};
	this.rows = {};
	this.switcher = false;
	this.info = {"count" : 0, "checked" : 0};
	this.counter = false;
	
	this.Init = function()
	{
		var res = false; var row = false; var res1 = false;
		oTable = document.getElementById(namespace + '_table')
		if (!oTable || oTable.tagName != 'TABLE')
			return false;
		for (var ii = 0; ii < oTable.rows.length; ii++)
		{
			row = oTable.rows[ii]; id = false;
			
			if (!row || !row.parentNode || row.parentNode.nodeName != 'TBODY')
				continue;

			res = row.getElementsByTagName("INPUT");
			for (var jj = 0; jj < res.length; jj++)
			{
				if (!res[jj] || !res[jj].name || !res[jj].type || res[jj].type.toLowerCase() != "checkbox" || res[jj].name == (this.namespace + '_all') ||
					(res[jj].name.replace(/[^a-z0-9_]/ig, "") != this.namespace))
					continue;
				id = res[jj].value;
				res[jj].onclick = new Function("this.checked=(!this.checked);")
				this.checkbox[id] = res[jj];
				this.info["count"]++;
				break;
			}
			if (id)
			{
				row.id = this.namespace + '_row_' + id;
				row.onmouseup = function(){t.onRowClick(this)};
				this.rows[id] = row;
				res = row.getElementsByTagName("A");
				for (var jj = 0; jj < res.length; jj++)
				{
					if (!res[jj])
						continue;
					res[jj].onmouseup = function(e){jsUtils.PreventDefault(e)};
				}
			}
			row.onmouseover = new Function("this.className+=' marked';");
			row.onmouseout = new Function("this.className=this.className.replace('marked', '');");
		}
		if (this.info["count"] > 0)
		{
			if (document.getElementById(this.namespace + '_all'))
			{
				this.switcher = document.getElementById(this.namespace + '_all');
				this.switcher.onclick = function(){t.selectAll()}
			}
			if (document.getElementById(this.namespace + '_counter'))
				this.counter = document.getElementById(this.namespace + '_counter');
		}
	}
	
	this.onRowClick = function(oRow, bChecked)
	{
		if (!oRow || !oRow.id)
			return false;
		var res = oRow.id.split("_row_");
		var id = parseInt(res[1]);
		if (!(id > 0))
			return false;

		if (!this.checkbox[id])
			return false;
		var oCheckBox = this.checkbox[id];
		bChecked = ((bChecked == "Y" || bChecked == "N") ? bChecked : "U");
		if (bChecked == "U")
			bChecked = (oCheckBox.checked ? "N" : "Y");
	
		if (bChecked == "N")
		{
			this.info["checked"]--;
			oCheckBox.checked = false;
			oRow.className = oRow.className.replace(/checked/gi, "");
			oRow.className = oRow.className.replace(/\s+/gi, " ");
		}
		else
		{
			this.info["checked"]++;
			oCheckBox.checked = true;
			oRow.className += " checked";
		}
		
		if (this.switcher)
		{
			if (this.info["checked"] == this.info["count"])
				this.switcher.checked = true;
			else
				this.switcher.checked = false;
		}
		if (this.counter)
			this.counter.innerHTML = this.info["checked"];
	}
	
	this.selectAll = function()
	{
		if (!this.switcher)
			return false;
		var res = (this.switcher.checked ? "Y" : "N");
		this.info["checked"] = (this.switcher.checked ? 0 : this.info["count"]);
		for (var ii in this.rows)
		{
			this.onRowClick(this.rows[ii], res);
		}
		return;
	}
}

function InitForumTable(id)
{
	var oObjectTable = new ForumTable(id);
	oObjectTable.Init();
}

FTableScriptLoaded = true;