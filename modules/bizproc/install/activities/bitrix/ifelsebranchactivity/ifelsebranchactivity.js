/////////////////////////////////////////////////////////////////////////////////////
// IfElseBranchActivity
/////////////////////////////////////////////////////////////////////////////////////

IfElseBranchActivity = function()
{
	var ob = new SequenceActivity();
	ob.Type = 'IfElseBranchActivity';
	ob.iHead = 1;

	ob.Draw = function (container)
	{
		ob.container = container;

		ob.childsContainer = container.appendChild(_crt(1 +  ob.iHead + ob.childActivities.length*2, 1));
		ob.childsContainer.className = 'seqactivitycontainer';
		ob.childsContainer.id = ob.Name;
		ob.childsContainer.style.height = '100%';


		var d = ob.childsContainer.rows[0].cells[0].appendChild(document.createElement('DIV'));
		d.style.margin = '0px auto';
		d.style.textAlign = 'center';
		d.style.width = '190px';
		d.style.height = '20px';
		d.style.marginTop = '7px';
		d.style.margin = '5px';
		d.innerHTML = '<div style="background: url(/bitrix/images/bizproc/cond_bg.gif);"><div style="background: url(/bitrix/images/bizproc/cond_r.gif) right top no-repeat;"><div style="background: url(/bitrix/images/bizproc/cond_l.gif) left top no-repeat; height: 23px; overflow-y: hidden;"><div style="padding-top: 5px; font-size: 12px; height: 23px; overflow-y: hidden; padding-right: 5px;"></div></div></div></div>';

		d.ondblclick = ob.OnSettingsClick;
	/*
		var d = ob.childsContainer.rows[0].cells[0].appendChild(document.createElement('DIV'));
		d.style.width = '100px';
		d.style.height = '14px';
		d.style.border = '1px #CCCCCC solid';
		d.style.backgroundColor = '#ededed';
		*/

		var t = d.childNodes[0].childNodes[0].childNodes[0].childNodes[0].appendChild(_crt(1, 2));
		t.rows[0].cells[0].innerHTML = BX.util.htmlspecialchars(ob.Properties['Title']);
		t.rows[0].cells[0].title = ob.Properties['Title'];
		t.rows[0].cells[0].width = '100%';
		t.rows[0].cells[0].style.fontSize = '11px';
		var setimg = t.rows[0].cells[1].appendChild(document.createElement('IMG'));
		t.rows[0].cells[1].width = '1%';
		t.rows[0].cells[1].vAlign = 'top';
		setimg.width = '12';
		setimg.height = '12';
		setimg.src = '/bitrix/images/bizproc/act_button_sett_gray.gif';
		setimg.onclick = ob.Settings;
		setimg.style.cursor = 'pointer';


		ob.CreateLine(0);

		for(var i in ob.childActivities)
		{
			if (!ob.childActivities.hasOwnProperty(i))
				continue;
			ob.childActivities[i].Draw(ob.childsContainer.rows[ob.iHead + i*2 + 1].cells[0]);
			ob.CreateLine(parseInt(i) + 1);
		}
	}

	return ob;
}
