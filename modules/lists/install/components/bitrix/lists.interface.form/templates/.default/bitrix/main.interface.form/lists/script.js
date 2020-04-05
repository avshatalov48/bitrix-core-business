function BxInterfaceForm(name, aTabs)
{
	this.name = name;
	this.aTabs = aTabs;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById('bx-lists-tab-content_'+tab_id);
		if(div.className == 'bx-lists-tab-content active')
			return;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById('bx-lists-tab-content_'+this.aTabs[i]);
			if(tab.className == 'bx-lists-tab-content active')
			{
				this.ShowTab(this.aTabs[i], false);
				tab.className = 'bx-lists-tab-content';
				break;
			}
		}

		this.ShowTab(tab_id, true);
		div.className = 'bx-lists-tab-content active';

		var hidden = document.getElementById(this.name+'_active_tab');
		if(hidden)
			hidden.value = tab_id;
	};

	this.ShowTab = function(tab_id, on)
	{
		var sel = (on? 'bx-lists-tab-active':'');
		document.getElementById('tab_cont_'+tab_id).className = 'bx-lists-tab '+sel;
	};
}

