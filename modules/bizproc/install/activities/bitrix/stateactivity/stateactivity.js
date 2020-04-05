/////////////////////////////////////////////////////////////////////////////////////
// StateActivity
/////////////////////////////////////////////////////////////////////////////////////
StateActivity = function()
{
	var ob = new BizProcActivity();
	ob.Type = 'StateActivity';

	ob.Draw = function (divC)
	{
		ob.main = divC.appendChild(document.createElement("TABLE"));
		ob.main.cellPadding = 0;
		ob.main.width='220';
		ob.main.cellSpacing = 0;
		ob.main.style.fontSize = '12px';
		ob.main.style.textAlign = 'left';
		var r = ob.main.insertRow(-1);
		r.id = ob.Name;
		var c = r.insertCell(-1);
		c.width = '5';
		c.style.background = 'url(/bitrix/images/bizproc/stat_hl.gif)';

		var d111 = r.insertCell(-1);
		//d111.width = '100%';
		d111.noWrap = 'nowrap';
		d111.style.background = 'url(/bitrix/images/bizproc/stat_hbg.gif)';
		d111.style.height = '24px';

		c = r.insertCell(-1);
		c.width = '5';
		c.style.background = 'url(/bitrix/images/bizproc/stat_hr.gif)';


		var th = d111.appendChild(document.createElement('TABLE'));
		th.width = '100%';
		th.cellPadding = 0;
		th.cellSpacing = 0;
		th.style.fontSize = '12px';

		r = th.insertRow(-1);

		c = r.insertCell(-1);

		var sp = c.appendChild(document.createElement('DIV'));
		sp.innerHTML = '<b>'+HTMLEncode(ob['Properties']['Title'])+'</b>';
		sp.style.padding = '5px';
		sp.style.marginLeft = '5px';
		sp.style.width = '160px';
		sp.style.overflow = 'hidden';
		sp.style.whiteSpace = 'nowrap';
		sp.title = ob['Properties']['Title'];
		sp.align = 'left';

		c = r.insertCell(-1);
		c.className = 'statset';
		c.onclick = ob.OnSettingsClick;

		c = r.insertCell(-1);
		c.className = 'statdel';
		c.onclick = ob.OnRemoveClick;


		r = ob.main.insertRow(-1);
		c = r.insertCell(-1);
		c.style.background = 'url(/bitrix/images/bizproc/stat_l.gif) left top repeat-y';

		var d2 = r.insertCell(-1);

		c = r.insertCell(-1);
		c.style.background = 'url(/bitrix/images/bizproc/stat_r.gif) right top repeat-y';

		d2.style.backgroundColor = '#ffffff';
		//d2.style.width = '100%';
		d2.style.padding = '5px';

		/***********/
		ob.commandTable = d2.appendChild(_crt(1, 1));
		ob.commandTable.cellPadding = '4';
		ob.commandTable.rows[0].cells[0].align="left";
		ob.commandTable.rows[0].cells[0].style.fontSize="12px";
		var addlnk = ob.commandTable.rows[0].cells[0].appendChild(jsUtils.CreateElement('A', {'href':'javascript:void(0)'}));
		addlnk.onclick = ob.ShowAddMenu;
		addlnk.style.textDecoration = 'none';
		addlnk.innerHTML = BPMESS['STATEACT_ADD']+' <img src="/bitrix/images/bizproc/add.gif" border="0">';

		for(var i=0; i<ob.childActivities.length; i++)
		{
			var title, rl =  ob.commandTable.insertRow(-1), cl = rl.insertCell(-1), ic;
			if(ob.childActivities[i]['Type'] == 'EventDrivenActivity')
			{
				title = ob.childActivities[i].childActivities[0]["Properties"]["Title"];
				if(ob.childActivities[i].childActivities[0].Type != 'DelayActivity')
					ic = 'cmd';
				else
					ic = 'delay';
			}
			else
			{
				if(ob.childActivities[i]["Properties"]["Title"])
					title = ob.childActivities[i]["Properties"]["Title"];
				if(ob.childActivities[i].Type == 'StateFinalizationActivity')
					ic = 'fin';
				else
					ic = 'init';
			}

			rl.id = ob.childActivities[i].Name;

			var cmdT = cl.appendChild(document.createElement('TABLE')), cmdR = cmdT.insertRow(-1), cmdC = cmdR.insertCell(-1);
			cmdT.style.fontSize="12px";
			cmdT.width = '100%';

			cmdC.width = 12;
			cmdC.height = 12;

			cmdC.style.background = 'url(/bitrix/images/bizproc/state'+ic+'.gif) center center no-repeat';

			cmdC = cmdR.insertCell(-1);
			cmdC.innerHTML = HTMLEncode(title);
			cmdC.onclick = ob.clickrow;
			cmdC.title = BPMESS['STATEACT_EDITBP'];


			cmdC = cmdR.insertCell(-1);
			cmdC.width = 20;
			cmdC.height = 12;
			cmdC.style.background = 'url(/bitrix/images/bizproc/state_set.gif) center center no-repeat';
			cmdC.title = BPMESS['STATEACT_SETT'];
			cmdC.onclick = ob.settings;


			cmdC = cmdR.insertCell(-1);
			cmdC.width = 12;
			cmdC.height = 12;
			cmdC.style.background = 'url(/bitrix/images/bizproc/state_del.gif) center center no-repeat';
			cmdC.title = BPMESS['STATEACT_DEL'];
			cmdC.onclick = ob.remove;

			cl.style.borderTop = '1px solid #e5e5e5';
			cl.style.fontSize="12px";
			cl.style.cursor = 'pointer';
			cl.onmouseover = function (e){this.style.backgroundColor='#f7f7f7';};
			cl.onmouseout = function (e){this.style.backgroundColor='#FFFFFF';};
		}

		r = ob.main.insertRow(-1);
		c = r.insertCell(-1);
		c.width = '5';
		c.style.background = 'url(/bitrix/images/bizproc/stat_bl.gif)';

		c = r.insertCell(-1);
		c.style.background = 'url(/bitrix/images/bizproc/stat_b.gif)';
		c.height = '5';

		c = r.insertCell(-1);
		c.width = '5';
		c.style.background = 'url(/bitrix/images/bizproc/stat_br.gif)';
	};

	ob.OnRemoveClick = function ()
	{
		ob.parentActivity.RemoveChild(ob);
	};

	ob.remove = function (e)
	{
		var id = this.parentNode.parentNode.parentNode.parentNode.parentNode.id;
		for(var i in ob.childActivities)
		{
			if (!ob.childActivities.hasOwnProperty(i))
			{
				continue;
			}
			if(ob.childActivities[i]['Name']==id)
			{
				ob.commandTable.deleteRow(parseInt(i)+1);
				ob.RemoveChild(ob.childActivities[i]);
				ob.parentActivity.DrawLines();
				break;
			}
		}
	};

	ob.settings = function (e)
	{
		var id = this.parentNode.parentNode.parentNode.parentNode.parentNode.id;
		for(var i in ob.childActivities)
		{
			if (!ob.childActivities.hasOwnProperty(i))
			{
				continue;
			}
			if(ob.childActivities[i]['Name']==id)
			{
				if(ob.childActivities[i].Type == 'EventDrivenActivity')
					ob.childActivities[i].childActivities[0].Settings();
				else
					ob.childActivities[i].Settings();
				break;
			}
		}
	};

	ob.clickrow = function (e)
	{
		var id = this.parentNode.parentNode.parentNode.parentNode.parentNode.id;
		for(var i in ob.childActivities)
		{
			if (!ob.childActivities.hasOwnProperty(i))
			{
				continue;
			}
			if(ob.childActivities[i]['Name']==id)
			{
				ob.SequentialShow(ob.childActivities[i]);
				break;
			}
		}
	};
	
	ob.HideRows = function ()
	{
		for(var i=0; i<ob.parentActivity.__l.length; i++)
			for(var j=0; j<5; j++)
				ob.parentActivity.__l[i][j].style.display = 'none';
	};

	ob.SequentialShow = function(act)
	{
		rootActivity._redrawObject = act;
		ob.parentActivity.Table.style.display = 'none';
		ob.HideRows();

		ob.__header = ob.parentActivity.Table.parentNode.appendChild(document.createElement('DIV'));
		ob.__header.style.fontSize = '12px';
		var link = ob.__header.appendChild(document.createElement('A'));
		link.href="javascript:void(0)";
		link.onclick=ob.SequentialHide;
		link.innerHTML = HTMLEncode(ob.Properties['Title']);
		var spn = ob.__header.appendChild(document.createElement('span'));
		spn.innerHTML = ' - '+(act.Type!='EventDrivenActivity' ? HTMLEncode(act.Properties['Title']) : HTMLEncode(act.childActivities[0].Properties['Title']) );
		ob.__seq = ob.parentActivity.Table.parentNode.appendChild(document.createElement('DIV'));
		ob.__footer = ob.parentActivity.Table.parentNode.appendChild(document.createElement('DIV'));
		var b = ob.__footer.appendChild(jsUtils.CreateElement('INPUT', {'type':"button", 'value': BPMESS['STATEACT_BACK']}));
		b.onclick = ob.SequentialHide;
		b.style.margin = '15px';
		if(document.getElementById("bizprocsavebuttons"))
			document.getElementById("bizprocsavebuttons").style.display = 'none';

		act.Draw(ob.__seq);
	};

	ob.SequentialHide = function()
	{
		try{
		ob.parentActivity.Table.style.display = 'table';
		}catch(e){
		ob.parentActivity.Table.style.display = 'block';
		}
		ob.__header.parentNode.removeChild(ob.__header);
		ob.__seq.parentNode.removeChild(ob.__seq);
		ob.__footer.parentNode.removeChild(ob.__footer);
		if(document.getElementById("bizprocsavebuttons"))
			document.getElementById("bizprocsavebuttons").style.display = 'block';
		rootActivity._redrawObject = null;
		arWorkflowTemplate = rootActivity.Serialize();
		ReDraw();
	};

	ob.AddInitialize = function ()
	{
		var r = ob.commandTable.insertRow(1);
		var c = r.insertCell(-1);
		c.innerHTML = '';
		var act = CreateActivity('StateInitializationActivity');
		ob.childActivities.push(act);
		act.parentActivity = ob;
		ob.SequentialShow(act);
	};

	ob.AddCommand = function ()
	{
		var act = CreateActivity('EventDrivenActivity');
		var act2 = CreateActivity('HandleExternalEventActivity');

		act.childActivities.push(act2);
		act2.parentActivity = act;

		var r = ob.commandTable.insertRow(1);
		var c = r.insertCell(-1);
		c.innerHTML = '';

		ob.childActivities.push(act);
		act.parentActivity = ob;

		act2.Settings();
	};

	ob.AddDelayActivity = function ()
	{
		var act = CreateActivity('EventDrivenActivity');
		var act2 = CreateActivity('DelayActivity');

		act.childActivities.push(act2);
		act2.parentActivity = act;

		var r = ob.commandTable.insertRow(1);
		var c = r.insertCell(-1);
		c.innerHTML = '';

		ob.childActivities.push(act);
		act.parentActivity = ob;

		act2.Settings();
	};

	ob.AddFinilize = function ()
	{
		var r = ob.commandTable.insertRow(1);
		var c = r.insertCell(-1);
		c.innerHTML = '';
		var act = CreateActivity('StateFinalizationActivity');
		//var act = 'StateFinalizationActivity';
		ob.childActivities.push(act);
		act.parentActivity = ob;
		ob.SequentialShow(act);
	};

	ob.ShowAddMenu = function (e)
	{
		ob.menu = new PopupMenu('state_float_menu');
		ob.menu.Create(2000);

		if(ob.menu.IsVisible())
			return;

		var bStart = false, bFinish = false;
		for(var i=0; i<ob.childActivities.length; i++)
		{
			if(ob.childActivities[i].Type == 'StateInitializationActivity')
				bStart = true;
			if(ob.childActivities[i].Type == 'StateFinalizationActivity')
				bFinish = true;
		}
		ob.menuItems = new Array();
		ob.menuItems.push({'ID': '2', 'TEXT':	BPMESS['STATEACT_MENU_COMMAND'], 'ONCLICK': "__StateActivityAdd('command', '"+ob.Name+"')"});
		ob.menuItems.push({'ID': '3', 'TEXT':	BPMESS['STATEACT_MENU_DELAY'], 'ONCLICK': "__StateActivityAdd('delay', '"+ob.Name+"')"});
		if(!bStart)
			ob.menuItems.push({'ID': '1', 'TEXT':	BPMESS['STATEACT_MENU_INIT'], 'ONCLICK': "__StateActivityAdd('init', '"+ob.Name+"')"});
		if(!bFinish)
			ob.menuItems.push({'ID': '5', 'TEXT':	BPMESS['STATEACT_MENU_FIN'], 'ONCLICK': "__StateActivityAdd('finish', '"+ob.Name+"')"});

		ob.menu.SetItems(ob.menuItems);
		ob.menu.BuildItems();

		var pos = jsUtils.GetRealPos(this);
		pos["bottom"]+=1;

		ob.menu.PopupShow(pos);
	};

	ob.RemoveResources = function ()
	{
		ob.main.parentNode.removeChild(ob.main);
	};

	return ob;
};

__StateActivityAdd = function (type, id)
{
	var act;
	for(var i=0; i<rootActivity.childActivities.length; i++)
	{
		if(rootActivity.childActivities[i]['Name']==id)
		{
			act = rootActivity.childActivities[i];

			switch(type)
			{
				case "init":
					act.AddInitialize();
					break;
				case "command":
					act.AddCommand();
					break;
				case "delay":
					act.AddDelayActivity();
					break;
				case "finish":
					act.AddFinilize();
					break;
			}
			break;
		}
	}
};
