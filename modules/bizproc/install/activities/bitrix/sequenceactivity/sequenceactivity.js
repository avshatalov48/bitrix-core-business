/////////////////////////////////////////////////////////////////////////////////////
// SequenceActivity
/////////////////////////////////////////////////////////////////////////////////////
var _SequenceActivityCurClick = null;
function _SequenceActivityClick(act_i, i)
{
	_SequenceActivityCurClick.AddActivity(CreateActivity({'Properties': {'Title': HTMLEncode(arAllActivities[act_i]['NAME'])}, 'Type': arAllActivities[act_i]['CLASS'], 'Children': []}), i);
}
function _SequenceActivityMyActivityClick(isn, i)
{
	if (
		arUserParams
		&& BX.type.isArray(arUserParams['SNIPPETS'])
		&& arUserParams['SNIPPETS'][isn]
	)
	{
		_SequenceActivityCurClick.AddActivity(CreateActivity(arUserParams['SNIPPETS'][isn]), i);
	}
}

SequenceActivity = function()
{
	var ob = new BizProcActivity();
	ob.Type = 'SequenceActivity';
	ob.childsContainer = null; //
	ob.iHead = 0;

	ob.LineMouseOver = function (e)
	{
		this.parentNode.style.backgroundImage = 'url(/bitrix/images/bizproc/arr_over.gif)';
	}

	ob.LineMouseOut = function (e)
	{
		this.parentNode.style.backgroundImage = 'url(/bitrix/images/bizproc/arr.gif)';
	}

	ob.OnClick = function (e)
	{
		/*
		var oActivity = eval("new "+"BizProcActivity"+"()");
		oActivity.Init({'id': Math.random(), 'childs': []});
		ob.AddActivity(oActivity, this.ind);
		*/
		_SequenceActivityCurClick = ob;
		var jsMnu_WFAct = [];
		var groupId, oSubMenu;
		for (groupId in arAllActGroups)
		{
			oSubMenu = [];
			for(var act_i in arAllActivities)
			{
				if (!arAllActivities.hasOwnProperty(act_i))
					continue;

				if (arAllActivities[act_i]["EXCLUDED"] || !arAllActivities[act_i]["CATEGORY"])
					continue;

				var activityGroupId = arAllActivities[act_i]["CATEGORY"]["ID"];
				if (arAllActivities[act_i]["CATEGORY"]["OWN_ID"])
					activityGroupId = arAllActivities[act_i]["CATEGORY"]["OWN_ID"];
				if (activityGroupId !=groupId)
					continue;

				if(act_i == 'setstateactivity' && rootActivity.Type == ob.Type)
					continue;

				oSubMenu.push({
					'ICON': 'url('+arAllActivities[act_i]['ICON']+')',
					'TEXT': '<img src="'+(arAllActivities[act_i]['ICON']?arAllActivities[act_i]['ICON']:'/bitrix/images/bizproc/act_icon.gif')+'" align="left" style="margin-right: 7px;margin-left: 0px">' + '<b>' + HTMLEncode(arAllActivities[act_i]['NAME']) + '</b><br>' + HTMLEncode(arAllActivities[act_i]['DESCRIPTION']),
					'ONCLICK': '_SequenceActivityClick(\''+act_i+'\', '+this.ind+');'
				});
			}

			if (groupId === 'rest' && BX.getClass('BX.rest.Marketplace'))
			{
				oSubMenu.push({
					'ICON': 'url(/bitrix/images/bizproc/act_icon_plus.png)',
					'TEXT': '<img src="/bitrix/images/bizproc/act_icon_plus.png" align="left" style="margin-right: 7px;margin-left: 0px">'
					+ '<b>' + HTMLEncode(BPMESS['BPSA_MARKETPLACE_ADD_TITLE']) + '</b><br>' + HTMLEncode(BPMESS['BPSA_MARKETPLACE_ADD_DESCR']),
					'ONCLICK': 'BX.rest.Marketplace.open({}, \'auto_pb\'); if(window.jsPopup_WFAct) {window.jsPopup_WFAct.PopupHide();}'
				});
			}

			if (oSubMenu.length > 0)
				jsMnu_WFAct.push({'TEXT': HTMLEncode(arAllActGroups[groupId]), 'MENU': oSubMenu});
		}

		if (arUserParams && BX.type.isArray(arUserParams['SNIPPETS']))
		{
			oSubMenu = [];
			for(var isn in arUserParams['SNIPPETS'])
			{
				if (!arUserParams['SNIPPETS'].hasOwnProperty(isn))
				{
					continue;
				}

				var icon = arUserParams['SNIPPETS'][isn]['Icon'];
				if (!icon)
				{
					icon = '/bitrix/images/bizproc/act_icon.gif';
				}
				var name = arUserParams['SNIPPETS'][isn]['Properties']['Title'];

				oSubMenu.push({'ICON': 'url('+icon+')', 'TEXT': '<img src="'+icon+'" align="left" style="margin-right: 7px;margin-left: 0px">' + '<b>' + HTMLEncode(name) + '</b>',
					'ONCLICK': '_SequenceActivityMyActivityClick(\''+isn+'\', '+this.ind+');'
				});
			}

			if (oSubMenu.length > 0)
				jsMnu_WFAct.push({'TEXT': HTMLEncode(BPMESS['BPSA_MY_ACTIVITIES']), 'MENU': oSubMenu});
		}

		if(window.jsPopup_WFAct)
			window.jsPopup_WFAct.PopupHide();
		else
			window.jsPopup_WFAct = new PopupMenu('PopupWFAct', 30000);

		window.jsPopup_WFAct.ShowMenu(this, jsMnu_WFAct); 
	};

	ob.lastDrop = false;
	ob.ondragging = function (e, X, Y)
	{
		if(!ob.childsContainer)
		 	return false;

		for(var i = 0; i <= ob.childActivities.length; i++)
		{
			var arrow = ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes[0];

			var pos = BX.pos(arrow);
			if(pos.left < X && X < pos.right
				&& pos.top < Y && Y < pos.bottom)
			{
				arrow.onmouseover();
				ob.lastDrop = arrow;
				return;
			}
		}

		if(ob.lastDrop)
		{
			ob.lastDrop.onmouseout();
			ob.lastDrop = false;
		}
	};

	ob.h1id = DragNDrop.AddHandler('ondragging', ob.ondragging);

	ob.ondrop = function (X, Y, e)
	{
		if(!ob.childsContainer)
		 	return false;

		if(ob.lastDrop)
		{
			var oActivity;
			if(DragNDrop.obj.parentActivity && e.ctrlKey == false)
			{

				var i, pos = -1, pa = DragNDrop.obj.parentActivity;
				for(i = 0; i<pa.childActivities.length; i++)
				{
					if(pa.childActivities[i].Name == DragNDrop.obj.Name)
					{
						pos = i;
						break;
					}
				}

				if(pa.Name != ob.Name || (pos != ob.lastDrop.ind && pos+1 != ob.lastDrop.ind))
				{
					var d = ob, s = false;

					while(d)
					{
						if(DragNDrop.obj.Name == d.Name)
						{
							s = true;
							break;
						}
						d = d.parentActivity;
					}

					if(s)
					{
						alert(BPMESS['BPSA_ERROR_MOVE']);
					}
					else
					{
						pa.childsContainer.deleteRow(pos*2 + 1 + pa.iHead);
						pa.childsContainer.deleteRow(pos*2 + 1 + pa.iHead);

						for(var j = pos; j<pa.childActivities.length - 1; j++)
							pa.childActivities[j] = pa.childActivities[j+1];

						pa.childActivities.pop();

						for(j = 0; j <= pa.childActivities.length; j++)
							pa.childsContainer.rows[j*2 + pa.iHead].cells[0].childNodes[0].ind = j;

						oActivity = DragNDrop.obj;
						ob.AddActivity(oActivity, ob.lastDrop.ind);
					}
				}
			}
			else
			{
				oActivity = CreateActivity(DragNDrop.obj);
				ob.AddActivity(oActivity, ob.lastDrop.ind);
			}
			ob.lastDrop.onmouseout();
			ob.lastDrop = false;
		}
	}

	ob.h2id = DragNDrop.AddHandler('ondrop', ob.ondrop);

	ob.ActivityRemoveChild = ob.RemoveChild;

	ob.RemoveChild = function (ch)
	{
		var i, j;
		for(i = 0; i<ob.childActivities.length; i++)
		{
			if(ob.childActivities[i].Name == ch.Name)
			{
				if(ob.childsContainer)
				{
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onmouseover = null;
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onmouseout = null;
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onclick = null;
				}

				ob.ActivityRemoveChild(ch);

				if(ob.childsContainer)
				{
					ob.childsContainer.deleteRow(i*2 + 1 + ob.iHead);
					ob.childsContainer.deleteRow(i*2 + 1 + ob.iHead);

					for(j = 0; j <= ob.childActivities.length; j++)
						ob.childsContainer.rows[j*2 + ob.iHead].cells[0].childNodes[0].ind = j;
				}

				break;
			}
		}
	}

	ob.RemoveResources = function (self)
	{
		//
		DragNDrop.RemoveHandler('ondragging', ob.h1id);
		DragNDrop.RemoveHandler('ondrop', ob.h2id);

		if(ob.childsContainer && ob.childsContainer.parentNode)
		{
			ob.childsContainer.parentNode.removeChild(ob.childsContainer);
			ob.childsContainer = null;
		}
	}

	ob.AddActivity = function (oActivity, pos)
	{
		var i;

		for(i = ob.childActivities.length; i>pos; i--)
			ob.childActivities[i] = ob.childActivities[i-1];

		ob.childActivities[pos] = oActivity;

		oActivity.parentActivity = ob;

		var c = ob.childsContainer.insertRow(pos*2 + 1 + ob.iHead).insertCell(-1);
		c.align = 'center';
		c.vAlign = 'center';

		oActivity.Draw(c);

		c = ob.childsContainer.insertRow(pos*2 + 2 + ob.iHead).insertCell(-1);
		c.align = 'center';
		c.vAlign = 'center';

		ob.CreateLine(pos+1);

		for(i = 0; i <= ob.childActivities.length; i++)
			ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes[0].ind = i;

		BPTemplateIsModified = true;
		//alert(document.styleSheets[0].rules[0]);
		//setTimeout(ob.DDD2, 110);
	}

	ob.CreateLine = function(ind)
	{
		ob.childsContainer.rows[ind*2 + ob.iHead].cells[0].style.height = '40px';
		ob.childsContainer.rows[ind*2 + ob.iHead].cells[0].style.background = 'url(/bitrix/images/bizproc/arr.gif) no-repeat scroll 50% 50%';

		var i = ob.childsContainer.rows[ind * 2 + ob.iHead].cells[0].appendChild(document.createElement('IMG'));
		i.src = '/bitrix/images/1.gif';
		i.width = '28';
		i.height = '21';
		i.onmouseover = ob.LineMouseOver;
		i.onmouseout = ob.LineMouseOut;
		i.onclick = ob.OnClick;
		i.ind = ind;
	}

	ob.ActivityDraw = ob.Draw;
	ob.Draw = function (container)
	{
		ob.childsContainer = container.appendChild(_crt(1 + ob.childActivities.length*2 + ob.iHead, 1));
		ob.childsContainer.className = 'seqactivitycontainer';
		ob.childsContainer.id = ob.Name;

		ob.CreateLine(0);
		for(var i in ob.childActivities)
		{
			if (!ob.childActivities.hasOwnProperty(i))
				continue;
			ob.childActivities[i].Draw(ob.childsContainer.rows[i*2 + 1 + ob.iHead].cells[0]);
			ob.CreateLine(parseInt(i) + 1);
		}

		if(ob.AfterSDraw)
			ob.AfterSDraw();
	}

	return ob;
}
