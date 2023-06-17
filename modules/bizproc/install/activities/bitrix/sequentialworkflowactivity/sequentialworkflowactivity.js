/////////////////////////////////////////////////////////////////////////////////////
// SequentialWorkflowActivity
/////////////////////////////////////////////////////////////////////////////////////
SequentialWorkflowActivity = function()
{
	var ob = new SequenceActivity();
	ob.Type = 'SequentialWorkflowActivity';
	ob.swfWorkspaceDiv = null;
	ob.swfToolBoxDiv = null;
	ob.Table = null;
	ob.hSnippid1 = false;
	
	ob.onScroll = function ()
	{
//		if(ob.Table == null)
//			return;

		var p = BX('bx-panel');
		if(!p)
			return;

		var d = 0;
		if(BX.hasClass(p, 'bx-panel-fixed'))
		{
		 	var panelSize = BX.pos(p);
			d = panelSize.height;
		}


	 	var scrollSize = BX.GetWindowInnerSize();
		var posW = BX.pos(ob.swfWorkspaceDiv);
		if(posW.height > scrollSize.innerHeight)
		{
			var pos = BX.pos(ob.Table.rows[0].cells[2]);
		 	var scrollPos = BX.GetWindowScrollPos();

			if(scrollPos.scrollTop > pos.top - d)
			{
				ob.swfToolboxDiv.style.position = 'fixed';
				ob.swfToolboxDiv.style.top = d + 'px';
				ob.swfToolboxDiv.style.overflowY = 'auto';
				ob.swfToolboxDiv.style.height = (scrollSize.innerHeight - d) + 'px';
				ob.swfToolboxDiv.style.width = '200px';
				ob.swfToolboxDiv.style.left = (pos.left - scrollPos.scrollLeft + 0) + 'px';
			}
			else if(scrollPos.scrollTop < pos.top - d)
			{
				ob.swfToolboxDiv.style.position = 'static';
			}

			if(scrollPos.scrollTop + scrollSize.innerHeight > pos.bottom)
			{
				ob.swfToolboxDiv.style.height = (pos.bottom - scrollPos.scrollTop - d) + 'px';
			}
		}
		else
		{
			ob.swfToolboxDiv.style.position = 'static';
		}
	};

	ob.UpdateButtonPanel = function()
	{
		if (BX.UI.ButtonPanel && BX.UI.ButtonPanel.pinner)
		{
			BX.UI.ButtonPanel.pinner.onChange();
		}
	};

	ob.DrawSequenceActivity = ob.Draw;
	ob.Draw = function (div)
	{
		requestAnimationFrame(() => {
			ob.UpdateButtonPanel();
		});
		if(!window.dlgSnippetsSettings)
		{
			window.dlgSnippetsSettings = new BX.CDialog({
				'content': '<table width="100%"><tr><td align="right" width="40%">'+BPMESS['SEQWF_SNIP_NAME']+'</td><td width="60%"><input type="hidden" id="snippsid" value=""><input size="40" id="snippstitle" value=""></td></tr><tr><td align="right" width="40%"></td><td width="60%" style="height:100px"></td></tr><tr><td align="right" width="40%"></td><td width="60%"><input type="button" id="snippsdel" value="'+BPMESS['SEQWF_SNIP_DEL_1']+'"></td></tr></table>',
				'title': BPMESS['SEQWF_SNIP_TITLE_1'],
				'height': 200,
				'width': 400,
				'buttons': [{
						title: BX.message('JS_CORE_WINDOW_SAVE'),
						id: 'savebtn',
						name: 'savebtn',
						action: function () 
							{
								var r = window.__lastSnippet;
								var ind = r._ind;
								arUserParams['SNIPPETS'][ind]['Properties']['Title'] = BX('snippstitle').value;
								r.childNodes[0].rows[0].cells[1].innerHTML = HTMLEncode(document.getElementById('snippstitle').value);
								this.parentWindow.Close();
								BCPSaveUserParams();
							}
						}, 
						BX.CDialog.prototype.btnCancel]
				});

			setTimeout(function ()
				{
					BX('snippsdel').onclick = function ()
					{
						if(confirm(BPMESS['SEQWF_CONF_1']))
						{
							var r = window.__lastSnippet;
							var ind = r._ind;

							arUserParams['SNIPPETS'].splice(ind, 1);

							var allP = r.parentNode;
							BX.remove(r);
							for(var i=1; i<allP.childNodes.length; i++)
								allP.childNodes[i]._ind = i-1;
							window.dlgSnippetsSettings.Close();
							BCPSaveUserParams();
						}
					}
				}, 1000
			);
		}

		ob.Table = div.appendChild(_crt(1, 3));

//		if(ob.Type == 'SequentialWorkflowActivity')
		{
			window.onscroll = ob.onScroll;
			window.onresize = ob.onScroll;
		}

		ob.hSnippid1 = DragNDrop.AddHandler('ondragging', ob.ondragging2);
		ob.hSnippid2 = DragNDrop.AddHandler('ondrop', ob.ondrop2);

		ob.swfWorkspaceDiv = ob.Table.rows[0].cells[0].appendChild(document.createElement('DIV'));
		ob.swfWorkspaceDiv.className = 'swfworkspace';
		if(parseInt(div.clientHeight)>50)
			ob.swfWorkspaceDiv.style.height = parseInt(div.clientHeight) + 'px';

		ob.ShowActivities(ob.Table.rows[0].cells[2]);


		ob._table = ob.swfWorkspaceDiv.appendChild(_crt(3, 1));
		//ob.Table.rows[0].cells[0].width = '82%';
		ob.Table.rows[0].cells[1].style.width = '0px';
		//ob.Table.rows[0].cells[1].style.borderLeft = '1px #d4d4d4 dotted';
		ob.Table.rows[0].cells[1].innerHTML = '<img src="/bitrix/images/1.gif" width="0">';
		ob.Table.rows[0].cells[2].style.width = '200px';

		ob.Table.rows[0].cells[0].vAlign = 'top';
		ob.Table.rows[0].cells[2].vAlign = 'top';
		ob.Table.rows[0].cells[2].align = 'left';
		ob.Table.rows[0].cells[2].appendChild(document.createElement('DIV')).style.width = '200px';


		var begin = ob._table.rows[0].cells[0].appendChild(document.createElement('DIV'));
		begin.style.margin = '0px auto';
		begin.style.textAlign = 'center';
		begin.style.width = '120px';
		begin.innerHTML = '<div style="background: url(/bitrix/images/bizproc/beg_bg.gif);"><div style="background: url(/bitrix/images/bizproc/beg_r.gif) right top no-repeat;"><div style="background: url(/bitrix/images/bizproc/beg_l.gif) left top no-repeat; height: 23px;"><div style="padding-top: 3px; font-size: 12px; color:#194d0b;">'+BPMESS['SEQWF_BEG']+'</div></div></div></div>';

		ob.DrawActivities();

		var end = ob._table.rows[2].cells[0].appendChild(document.createElement('DIV'));
		end.style.margin = '0px auto';
		end.style.textAlign = 'center';
		end.style.width = '120px';
		end.innerHTML = '<div style="background: url(/bitrix/images/bizproc/beg_bg.gif);"><div  style="background: url(/bitrix/images/bizproc/beg_r.gif) right top no-repeat;"><div style="background: url(/bitrix/images/bizproc/beg_l.gif) left top no-repeat; height: 23px;"><div style="padding-top: 3px; font-size: 12px; color:#194d0b;">'+BPMESS['SEQWF_END']+'</div></div></div></div>';
	};

	ob.DrawActivities = function ()
	{
		while(ob._table.rows[1].cells[0].childNodes.length>0)
			ob._table.rows[1].cells[0].removeChild(ob._table.rows[1].cells[0].childNodes[0]);

		ob.DrawSequenceActivity(ob._table.rows[1].cells[0]);
	};

	ob.DrawGroup = function (oGroup)
	{
		var ind = ob.swfToolboxDiv.childNodes.length;
		var divGroup = ob.swfToolboxDiv.appendChild(document.createElement('DIV'));
		if(!arUserParams['TOOLBOX_GROUPS'])
			arUserParams['TOOLBOX_GROUPS'] = [];
		if(arUserParams['TOOLBOX_GROUPS'][ind] == true)
			divGroup.className = 'swftoolboxgroupopened';
		else
			divGroup.className = 'swftoolboxgroupclosed';
		divGroup.swftoolboxid = ind;
		divGroup.onclick = function (e) 
		{
			requestAnimationFrame(() => {
				ob.UpdateButtonPanel();
			});
			if(this.className=='swftoolboxgroupclosed')
			{
				this.className = 'swftoolboxgroupopened';
				arUserParams['TOOLBOX_GROUPS'][this.swftoolboxid] =  true;
			}
			else
			{
				this.className = 'swftoolboxgroupclosed';
				arUserParams['TOOLBOX_GROUPS'][this.swftoolboxid] =  false;
			}

			BCPSaveUserParams();
		};

		var divGroupHeader = divGroup.appendChild(document.createElement('DIV'));
		divGroupHeader.className = 'swftoolboxgroupheader';
		divGroupHeader.innerHTML = '<div class="t"><div class="tr"><div class="tl"><div class="imarr"></div><div class="swftoolboxgrheadtext" title="'+HTMLEncode(oGroup)+'">'+HTMLEncode(oGroup)+'</div></div></div></div>';

		var divGroupList = divGroup.appendChild(document.createElement('DIV'));
		divGroupList.className = 'swftoolboxgrouplist';

		return divGroupList;
	};
	
	ob.DrawGroupItem = function (divGroupList, oActivity, bExt, ind)
	{
		var dCont, bCat, cat;

		dCont = divGroupList.appendChild(document.createElement('DIV'));
		dCont.onclick = function(e){BX.PreventDefault(e);};
		if(oActivity['NAME'] !== undefined)
			dCont.activityTemplate = {'Properties': {'Title': oActivity['NAME']}, 'Type': oActivity['CLASS'], 'Children': [], 'Icon': oActivity['ICON']};
		else
			dCont.activityTemplate = oActivity;

		var t = dCont.appendChild(_crt(1, 3));

		t.rows[0].style.height = '30px';
		t.rows[0].cells[0].style.width = '30px';
		t.rows[0].cells[0].style.minWidth = '30px';

		if(!dCont.activityTemplate['Icon'] && arAllActivities[dCont.activityTemplate['Type'].toLowerCase()])
			dCont.activityTemplate['Icon'] = arAllActivities[dCont.activityTemplate['Type'].toLowerCase()]['ICON'];

		if(dCont.activityTemplate['Icon'])
			t.rows[0].cells[0].style.background = 'url('+dCont.activityTemplate['Icon']+') 3px 3px no-repeat';
		else
			t.rows[0].cells[0].style.background = 'url(/bitrix/images/bizproc/act_icon.gif) 3px 3px no-repeat';

		//d.style.borderBottom = "1px #EBEBEB solid"
		t.rows[0].cells[0].style.cursor = 'pointer';

		t.rows[0].cells[1].style.cursor = 'pointer';
		t.rows[0].cells[1].style.fontSize = '11px';
		t.rows[0].cells[1].innerHTML = HTMLEncode(dCont.activityTemplate['Properties']['Title']);
		t.rows[0].cells[1].align = 'left';

		if(bExt)
		{
			t.rows[0].cells[2].style.width = '14px';
			t.rows[0].cells[2].style.cursor = 'pointer';
			t.rows[0].cells[2].innerHTML = '<img src="/bitrix/themes/.default/public/popup/pencil.gif" hspace="2">';
			t.rows[0].cells[2].onmousedown = function (e)
			{
				return BX.PreventDefault(e);
			};
			
			dCont._ind = ind;

			t.rows[0].cells[2].onclick = function (e)
			{
				window.__lastSnippet = this.parentNode.parentNode.parentNode.parentNode;

				BX('snippstitle').value = dCont.activityTemplate['Properties']['Title'];
				BX('snippsid').value = ind;

				window.dlgSnippetsSettings.Show();
		
				return BX.PreventDefault(e);
			}
		}

		t.insertRow(-1);
		t.rows[1].insertCell(-1).innerHTML = '<table width="100%" style="border-collapse: collapse" cellpadding="0" cellspacing="0" border="0"><tr><td width="5"></td><td style="border-bottom: 1px #EBEBEB solid; height: 1px; font-size: 1px;"><img src="/bitrix/images/1.gif" width="1" height="1"></td><td width="5"></td></tr></table>';
		t.rows[1].cells[0].colSpan = "3";

		dCont.onmousedown = function (e)
		{
			if(!e)
				e = window.event;

			var div = DragNDrop.StartDrag(e, this.activityTemplate);

			div.innerHTML = this.innerHTML;
			div.style.width = this.parentNode.offsetWidth + 'px';
		}
		
	};

	ob.DrawMarketplaceItem = function (divGroupList)
	{
		var dCont, bCat, cat;

		dCont = divGroupList.appendChild(document.createElement('DIV'));
		dCont.onclick = function(e) {
			BX.PreventDefault(e);
			BX.rest.Marketplace.open({}, 'auto_pb');
		};

		var t = dCont.appendChild(_crt(1, 3));

		t.rows[0].style.height = '30px';
		t.rows[0].cells[0].style.width = '30px';

		t.rows[0].cells[0].style.background = 'url(/bitrix/images/bizproc/act_icon_plus.png) 3px 3px no-repeat';

		//d.style.borderBottom = "1px #EBEBEB solid"
		t.rows[0].cells[0].style.cursor = 'pointer';

		t.rows[0].cells[1].style.cursor = 'pointer';
		t.rows[0].cells[1].style.fontSize = '11px';
		t.rows[0].cells[1].innerHTML = HTMLEncode(BPMESS['SEQWF_MARKETPLACE_ADD']);
		t.rows[0].cells[1].align = 'left';

		t.insertRow(-1);
		t.rows[1].insertCell(-1).innerHTML = '<table width="100%" style="border-collapse: collapse" cellpadding="0" cellspacing="0" border="0"><tr><td width="5"></td><td style="border-bottom: 1px #EBEBEB solid; height: 1px; font-size: 1px;"><img src="/bitrix/images/1.gif" width="1" height="1"></td><td width="5"></td></tr></table>';
		t.rows[1].cells[0].colSpan = "3";
	};

	ob.ShowActivities = function (div)
	{
		ob.swfToolboxDiv = div.appendChild(document.createElement('DIV'));
		if(parseInt(div.clientHeight)>50)
			ob.swfToolboxDiv.style.height = parseInt(div.clientHeight) + 'px';
		ob.swfToolboxDiv.style.overflowX = 'hidden';
		ob.swfToolboxDiv.className = 'swftoolbox';

		var groupId, divGroupList;
		for (groupId in arAllActGroups)
		{
			divGroupList = null;

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

				if (divGroupList === null)
					divGroupList = ob.DrawGroup(arAllActGroups[groupId]);

				ob.DrawGroupItem(divGroupList, arAllActivities[act_i]);

				const presets = arAllActivities[act_i]['PRESETS'];

				if (BX.Type.isArrayFilled(presets))
				{
					presets.forEach((preset) => {
						const activity = {
							Properties: {
								Title: preset['NAME'],
								...preset['PROPERTIES'],
							},
							Type: arAllActivities[act_i]['CLASS'],
							Children: [],
							Icon: arAllActivities[act_i]['ICON']
						};

						ob.DrawGroupItem(divGroupList, activity);
					});
				}
			}

			if (groupId === 'rest' && BX.getClass('BX.rest.Marketplace'))
			{
				if (divGroupList === null)
				{
					divGroupList = ob.DrawGroup(arAllActGroups[groupId]);
				}

				ob.DrawMarketplaceItem(divGroupList);
			}
		}

		var drdrop = ob.DrawGroup(BPMESS['SEQWF_SNIP_1']);
		ob.divSnippets = drdrop;

		var cntmp = drdrop.appendChild(document.createElement('DIV'));
		cntmp.style.padding = '4px';
		var dCont = cntmp.appendChild(document.createElement('DIV'));
		dCont.style.padding = '7px 5px 7px 5px';
		//dCont.style.margin = '4px';
		dCont.style.textAlign = 'center';
		dCont.style.color = '#AAAAAA';
		dCont.style.border = '1px dashed #DDDDDD';
		dCont.innerHTML = BPMESS['SEQWF_SNIP_DD_1'];
		ob.drdrop = dCont;

		arUserParams['SNIPPETS'] = arUserParams['SNIPPETS'] || [];
		for(var isn in arUserParams['SNIPPETS'])
		{
			if (!arUserParams['SNIPPETS'].hasOwnProperty(isn))
				continue;
			ob.DrawGroupItem(ob.divSnippets, arUserParams['SNIPPETS'][isn], true, isn);
		}
	};

	ob.lastDrop2 = false;
	ob.ondragging2 = function (e, X, Y)
	{
		//console.debug(ob);
		var pos = BX.pos(ob.drdrop);
		if(pos.left < X && X < pos.right
			&& pos.top < Y && Y < pos.bottom)
		{
			//arrow.onmouseover();
			ob.drdrop.style.border = '1px dashed #333333';
			ob.lastDrop2 = true;
			return;
		}

		if(ob.lastDrop2)
		{
			//.onmouseout();
			ob.drdrop.style.border = '1px dashed #DDDDDD';
			ob.lastDrop2 = false;
		}
	};

	ob.ondrop2 = function (e, X, Y)
	{
		if(ob.lastDrop2)
		{
			var oActivity = CreateActivity(DragNDrop.obj);
			ob.DrawGroupItem(ob.divSnippets, oActivity, true, arUserParams['SNIPPETS'].length);

			arUserParams['SNIPPETS'].push(oActivity.Serialize());
			BCPSaveUserParams();

			ob.drdrop.style.border = '1px dashed #DDDDDD';
			ob.lastDrop2 = false;
		}
	};


	ob.RemoveResourcesSequenceActivity = ob.RemoveResources;
	ob.RemoveResources = function ()
	{
		requestAnimationFrame(() => {
			ob.UpdateButtonPanel();
		});
		if(ob.hSnippid1)
		{
			DragNDrop.RemoveHandler('ondragging', ob.hSnippid1);
			DragNDrop.RemoveHandler('ondrop', ob.hSnippid2);
			ob.hSnippid1 = false;
		}

		ob.RemoveResourcesSequenceActivity();
		if(ob.Table)
		{
			ob.Table.parentNode.removeChild(ob.Table);
			ob.Table = null;
		}
	};

	return ob;
};
