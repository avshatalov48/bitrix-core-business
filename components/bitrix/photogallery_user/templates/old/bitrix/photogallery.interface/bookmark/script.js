/************************************************/
function PhotoTabControl(unique_name, active)
{
	var _this = this;
	this.object_id = unique_name;
	this.active_id = active;
	this.tabs = {};
	this.bReady = true;

	this.SelectTab = function(tab_id)
	{
		if (tab_id == this.active_id)
			return;
		else if (!this.bReady)
			return;
		this.HideTab(this.active_id);
		this.ShowTab(tab_id);
		this.active_id = tab_id;
	}

	this.ShowTab = function(tab_id, on)
	{
		var tab = document.getElementById('header_' + this.object_id + '_' + tab_id);
		if (tab)
		{
			tab.className = 'active';
		}
		tab = document.getElementById('body_' + this.object_id + '_' + tab_id);
		if (tab)
		{
			tab.style.display = '';
		}
	}
	
	this.HideTab = function(tab_id)
	{
		var tab = document.getElementById('header_' + this.object_id + '_' + tab_id);
		if (tab)
		{
			tab.className = 'no-active';
		}
		tab = document.getElementById('body_' + this.object_id + '_' + tab_id);
		if (tab)
		{
			tab.style.display = 'none';
		}
	}
	
	this.SendAjax = function(path, tab_id)
	{
		if (typeof path != "string" || path == "null" || path == "")
			return false;
		if (this.tabs[tab_id] == "sended")
			return false;
		

		TID = CPHttpRequest.InitThread();
		CPHttpRequest.SetAction(TID, function(data){
			try
			{
				document.getElementById("body_" + _this.object_id + '_' + _this.active_id).innerHTML = data;
				oPhotoTabs[_this.object_id].tabs[_this.active_id] = "sended";
			}
			catch(e){}
			_this.bReady = true;
		});
		this.bReady = false;
		CPHttpRequest.Send(TID, path, {"AJAX_CALL" : "Y"});
		return false;
	}

	this.HoverTab = function(tab_id)
	{
		var tab = document.getElementById('td_' + this.object_id + '_' + tab_id);
		if (!tab)
			return;
		if(tab.className == 'tab-selected')
			return;

		tab.className = (on? 'tab-hover':'tab');
	}
}
/************************************************/