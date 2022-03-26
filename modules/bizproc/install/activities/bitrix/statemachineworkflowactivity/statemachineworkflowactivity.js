/////////////////////////////////////////////////////////////////////////////////////
// StateMachineWorkflowActivity
/////////////////////////////////////////////////////////////////////////////////////
StateMachineWorkflowActivity = function()
{
	var ob = new BizProcActivity();
	ob.classname = 'StateMachineWorkflowActivity';

	ob.Table = null;
	ob.__l = [];

	ob.SerializeStateMachineWorkflowActivity = ob.Serialize;
	ob.Serialize = function ()
	{
		if(ob.childActivities.length>0)
			ob.Properties['InitialStateName'] = ob.childActivities[0]['Name'];
		return ob.SerializeStateMachineWorkflowActivity();
	};

	ob.LineMouseOver = function (e)
	{
		var id = this.id;
		id = id.substring(0, id.length - 2);
		for(var i=1; i<=3; i++)
		{
			var el = document.getElementById(id+'.'+i);
			el.style.backgroundColor = '#ee0000';
			el.style.zIndex = parseInt(el.style.zIndex) + 1000;
		}
	};

	ob.LineMouseOut = function (e)
	{
		var id = this.id;
		id = id.substring(0, id.length - 2);
		for(var i=1; i<=3; i++)
		{
			var el = document.getElementById(id+'.'+i);
			el.style.backgroundColor = '#afb8d2';
			el.style.zIndex = parseInt(el.style.zIndex) - 1000;
		}
	};

	ob.DrawLines = function ()
	{
		var i, j;
		for(i=0; i<ob.__l.length; i++)
			for(j=0; j<ob.__l[i].length; j++)
				ob.__l[i][j].parentNode.removeChild(ob.__l[i][j]);

		ob.__l = [];
		ob.StatusArrows = [];
		ob.FindSetState(false, ob);
		var ar = [], arUnsorted = ob.StatusArrows, pairId, paired = {};
		for(i=0; i<arUnsorted.length; i++)
		{
			pairId = arUnsorted[i][0] + '|' + arUnsorted[i][1];
			if (!(pairId in paired))
			{
				paired[pairId] = true;
				ar.push(arUnsorted[i]);
			}
		}

		var linesLeft = 0, linesCenter = 0, linesRight = 0;
		for(i=0; i<ar.length; i++)
		{
			var from = ActGetRealPos(document.getElementById(ar[i][0]));
			var to = ActGetRealPos(document.getElementById(ar[i][1]));

			if(from===false || to===false || from['left']<=0 || to['left']<=0)
				continue;

			var lineCont = ob.Table.parentNode;
			var d0 = lineCont.appendChild(document.createElement("IMG"));
			d0.style.position = 'absolute';
			d0.style.zIndex = 14+i*10;
			d0.width = '8';
			d0.height = '8';
			var d4 = lineCont.appendChild(document.createElement("IMG"));
			d4.style.position = 'absolute';
			d4.style.zIndex = 15+i*10;
			d4.width = '8';
			d4.height = '8';
			var d1 = lineCont.appendChild(document.createElement("DIV"));
			d1.style.position = 'absolute';
			d1.style.backgroundColor = '#afb8d2';
			d1.style.zIndex = 11+i*10;
			d1.style.height = '2px';
			d1.id = ar[i][0]+'-'+ar[i][1]+'.1';
			d1.onmouseover = ob.LineMouseOver;
			d1.onmouseout = ob.LineMouseOut;

			var d2 = lineCont.appendChild(document.createElement("DIV"));
			d2.style.position = 'absolute';
			d2.style.backgroundColor = '#afb8d2';
			d2.style.zIndex = 12+i*10;
			d2.style.width = '2px';
			d2.id = ar[i][0]+'-'+ar[i][1]+'.2';
			d2.onmouseover = ob.LineMouseOver;
			d2.onmouseout = ob.LineMouseOut;

			var d3 = lineCont.appendChild(document.createElement("DIV"));
			d3.style.position = 'absolute';
			d3.style.backgroundColor = '#afb8d2';
			d3.style.zIndex = 13+i*10;
			d3.style.height = '2px';
			d3.id = ar[i][0]+'-'+ar[i][1]+'.3';
			d3.onmouseover = ob.LineMouseOver;
			d3.onmouseout = ob.LineMouseOut;

			var x = d3.appendChild(document.createElement('IMG'));
			x.width = '1';
			x.height = '1';

			x = d1.appendChild(document.createElement('IMG'));
			x.width = '1';
			x.height = '1';

			from['right'] = from['right'] + 10;
			from['left'] = from['left'] - 10;

			var W, C = -50, D = 12, direction = -1;
			if(from['right']<to['left'])
			{
				++linesCenter;
				C += (linesCenter % 6)*6;

				W = to['left'] - from['right'];
				d1.style.left = from['right'] + 'px';
				d1.style.width = W/2 + C + 'px';
				d2.style.left = from['right'] + W/2 + C + 'px';
				d3.style.left = from['right'] + W/2 + C + 'px';
				d0.src = '/bitrix/images/bizproc/arr_l1.gif';
				d0.style.left = from['right'] - 2 + 'px';
				d0.style.top = from['top'] + D - 3 + 'px';
				d4.src = '/bitrix/images/bizproc/arr_r1.gif';
				d4.style.left = to['left']+ 'px';
				d4.style.top = to['top'] + D - 3 + 'px';
			}
			else if(from['left'] === to['left'])
			{
				W = 150;
				var columnNode = BX.findParent(document.getElementById(ar[i][0]), {attr: 'data-column'});
				if (columnNode && columnNode.getAttribute('data-column') === '2')
				{
					++linesRight;
					C += (linesRight % 10)*10;

					d0.src = '/bitrix/images/bizproc/arr_l1.gif';
					d0.style.left = from['right'] - 2 + 'px';
					d0.style.top = from['top'] + D - 3 + 'px';
					d1.style.left = from['right'] + 'px';
					d1.style.width = W/2 + C + 'px';
					d2.style.left = from['right'] + W/2 + C + 'px';
					d3.style.left = to['right']+ 'px';
					d4.src = '/bitrix/images/bizproc/arr_l2.gif';
					d4.style.left = to['right'] - 8 + 'px';
					d4.style.top = to['top'] + D - 3 + 'px';
					direction = 1;
				}
				else
				{
					++linesLeft;
					C += (linesLeft % 10)*10;

					d1.style.left = from['left'] - W / 2 + C + 'px';
					d1.style.width = W / 2 - C + 'px';
					d2.style.left = from['left'] - W / 2 + C + 'px';
					d3.style.left = from['left'] - W / 2 + C + 'px';
					d0.src = '/bitrix/images/bizproc/arr_l2.gif';
					d0.style.left = from['left'] - 6 + 'px';
					d0.style.top = from['top'] + D - 3 + 'px';
					d4.src = '/bitrix/images/bizproc/arr_r1.gif';
					d4.style.left = to['left'] + 'px';
					d4.style.top = to['top'] + D - 3 + 'px';
				}
			}
			else
			{
				++linesCenter;
				C = (linesCenter % 6)*6 - 50;

				W = from['left'] - to['right'];
				d1.style.left = to['right'] + W/2 - C+ 'px';
				d1.style.width = W/2 + C+ 'px';
				d2.style.left = to['right'] + W/2 -C+ 'px';
				d3.style.left = to['right']+ 'px';

				d0.src = '/bitrix/images/bizproc/arr_r2.gif';
				d0.style.left = from['left'] - 6+ 'px';
				d0.style.top = from['top'] + D - 3+ 'px';
				d4.src = '/bitrix/images/bizproc/arr_l2.gif';
				d4.style.left = to['right'] - 8+ 'px';
				d4.style.top = to['top'] + D - 3+ 'px';
			}

			d1.style.top = from['top'] + D+ 'px';

			if(from['top']<to['top'])
			{
				d2.style.top = from['top'] + D+ 'px';
				d2.style.height = to['top'] - from['top']+ 'px';
			}
			else
			{
				d2.style.top = to['top'] + D+ 'px';
				d2.style.height = from['top'] - to['top']+ 'px';
			}

			d3.style.top = to['top'] + Math.floor((to['bottom'] - to['top'])/2)+ 'px';

			d3.style.width = (W/2 + direction * C + 2) + 'px';

			ob.__l.push([d0, d1, d2, d3, d4]);
		}
	};

	ob.FindSetState = function (f, act)
	{
		if(act.Type == 'SetStateActivity')
		{
			if(act.Properties['TargetStateName'])
				ob.StatusArrows.push([f, act.Properties['TargetStateName']]);
		}
		else
		{
			for(var i=0; i<act.childActivities.length; i++)
			{
				if(act.Type == 'StateActivity')
					f = act.childActivities[i].Name;
				ob.FindSetState(f, act.childActivities[i]);
			}
		}
	};

	ob.Draw = function (div)
	{
		ob.statediv = div;
		ob.Table = div.appendChild(_crt(ob.childActivities.length + 1, 3));
		ob.Table.onresize = function (){/*alert('1');*/};

		var i;
		for(i=0; i<ob.childActivities.length; i++)
		{
			ob.Table.rows[i].cells[0].align = 'right';
			ob.Table.rows[i].cells[0].setAttribute('data-column', 1);

			ob.Table.rows[i].cells[2].align = 'left';
			ob.Table.rows[i].cells[2].setAttribute('data-column', 2);

			ob.childActivities[i].Draw(ob.Table.rows[i].cells[i%2*2]);
		}

		i = ob.childActivities.length;
		ob.Table.rows[i].cells[0].align = 'right';
		ob.Table.rows[i].cells[0].width = "350px";
		ob.Table.rows[i].cells[1].width = "150px";
		ob.Table.rows[i].cells[2].align = 'left';
		//ob.Table.rows[i].cells[2].width = "45%";
		var but = jsUtils.CreateElement('INPUT', {type: 'button', value: BPMESS['STM_ADD_STATUS_1']});
		but.style.marginBottom = '20px';
		ob.Table.rows[i].cells[i%2*2].appendChild(but);
		ob.Table.rows[i].cells[(i+1)%2*2].innerHTML = '&nbsp';
		but.onclick = ob.AddStatus;

		ob.ReCheckPosition(true);
	};

	ob.AddStatus = function (e)
	{
		var cnt = ob.childActivities.length;
		var act = CreateActivity('StateActivity');
		ob.childActivities.push(act);
		act.parentActivity = ob;

		act.Draw(ob.Table.rows[cnt].cells[cnt%2*2]);

		var r = ob.Table.insertRow(-1);
		r.insertCell(-1).align = 'right';
		r.insertCell(-1).align = 'center';
		r.insertCell(-1).align = 'left';


		cnt++;

		ob.Table.rows[cnt].cells[cnt%2*2].appendChild(this);

		act.Settings();
	};

	ob.RemoveChildStateMachine = ob.RemoveChild;
	ob.RemoveChild = function (act)
	{
		ob.RemoveChildStateMachine(act);
		ob.Table.parentNode.removeChild(ob.Table);
		ob.Draw(ob.statediv);
	};

	ob.ReplaceChild = function (act1, act2)
	{
		var index1 = ob.childActivities.indexOf(act1);
		var index2 = ob.childActivities.indexOf(act2);

		if (index1 < 0 || index2 < 0)
		{
			return;
		}

		ob.childActivities[index1] = act2;
		ob.childActivities[index2] = act1;

		BPTemplateIsModified = true;
		ob.RemoveResources();
		ob.Draw(ob.statediv);
	};


	ob.RemoveResourcesActivity = ob.RemoveResources;
	ob.RemoveResources = function ()
	{
		ob.RemoveResourcesActivity();
		if(ob.Table)
		{
			ob.Table.parentNode.removeChild(ob.Table);
			ob.Table = null;
		}
	};

	ob.ReCheckPosition = function (m)
	{
		if(ob.Table.style.display == 'none')
			return;
		var pos = ActGetRealPos(ob.Table);
		if(m || ob.__lpos != pos["left"] || ob.__rpos != pos["right"])
		{
			ob.__lpos = pos["left"];
			ob.__rpos = pos["right"];
			ob.DrawLines();
		}
		setTimeout(function () {ob.ReCheckPosition.call(ob);}, 1000);
	};

	return ob;
};
