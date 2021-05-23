/************************************************/
function TabControl(name, unique_name, aTabs)
{
	var _this = this;
	this.name = name;
	this.unique_name = unique_name;
	this.aTabs = aTabs;
	this.aTabsDisabled = {};
	this.bExpandTabs = false;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById(tab_id);
		if (!div)
			return;
		if(div.style.display != 'none')
			return;

		for(var i in this.aTabs)
		{
			var tab = document.getElementById(this.aTabs[i]["DIV"])
			if (!tab)
				return;
			if(tab.style.display != 'none')
			{
				this.ShowTab(this.aTabs[i]["DIV"], false);
				tab.style.display = 'none';
				break;
			}
		}

		this.ShowTab(tab_id, true);
		div.style.display = 'block';
		if (document.getElementById(this.name+'_active_tab'))
			document.getElementById(this.name+'_active_tab').value = tab_id;

		for(var i in this.aTabs)
			if(this.aTabs[i]["DIV"] == tab_id)
			{ 
				this.aTabs[i]["_ACTIVE"] = true;
				if(this.aTabs[i]["ONSELECT"])
					eval(this.aTabs[i]["ONSELECT"]);
				break;
			}
	}

	this.ShowTab = function(tab_id, on)
	{
		var sel = (on? '-selected':'');
		var tab_cont = document.getElementById('tab_cont_'+tab_id);
		var tab = document.getElementById('tab_'+tab_id);
		if (on)
		{
			if (tab_cont)
				tab_cont.className = tab_cont.className.replace(/tab\-container/gi, 'tab-container-selected');
			if (tab)
				tab.className = tab.className.replace(/tab/gi, 'tab-selected');
		}
		else
		{
			if (tab_cont)
				tab_cont.className = tab_cont.className.replace(/tab\-container\-selected/gi, 'tab-container');
			if (tab)
				tab.className = tab.className.replace(/tab\-selected/gi, 'tab');
		}
	}

	this.HoverTab = function(tab_id, on)
	{
		var tab = document.getElementById('tab_'+tab_id);
		if (tab)
			return;
		if (tab.className.search(/tab\-selected/gi) != -1)
			return;
		else if (on)
			tab.className = tab.className.replace(/tab/gi, 'tab-hover');
		else
			tab.className = tab.className.replace(/tab\-hover/gi, 'tab');
	}

	this.InitEditTables = function()
	{
		for(var tab in this.aTabs)
		{
			var div = document.getElementById(this.aTabs[tab]["DIV"]);
			if (!div || div == null || typeof(div) != "object")
				return false;
			var tbl = jsUtils.FindChildObject(div, 'table', 'edit-table');
			if(!tbl)
				continue;

			var n = tbl.rows.length;
			for(var i=0; i<n; i++)
				if(tbl.rows[i].cells.length > 1)
					tbl.rows[i].cells[0].className = 'field-name';
		}
	}

	this.Destroy = function()
	{
		for(var i in this.aTabs)
		{
			var tab = document.getElementById('tab_cont_'+this.aTabs[i]["DIV"]);
			if (!tab)
				continue;
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		_this = null;
	}
}
/************************************************/