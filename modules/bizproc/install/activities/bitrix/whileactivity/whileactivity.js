/////////////////////////////////////////////////////////////////////////////////////
// WhileActivity
/////////////////////////////////////////////////////////////////////////////////////
WhileActivity = function()
{
	var ob = new BizProcActivity();
	ob.Type = 'WhileActivity';

	ob.BizProcActivityDraw = ob.Draw;
	ob.Draw = function (container)
	{
		if(ob.childActivities.length == 0 )
		{
			ob.childActivities = [new SequenceActivity()];
			ob.childActivities[0].parentActivity = ob;
		}

		ob.container = container.appendChild(document.createElement('DIV'));
		if(!jsUtils.IsIE())
			ob.container.className = 'parallelcontainer';

		ob.BizProcActivityDraw(ob.container);
		ob.activityContent = null;

		ob.div.style.position = 'relative';
		ob.div.style.top = '12px';

		ob.hideContainer = ob.container.appendChild(document.createElement('DIV'));
		ob.hideContainer.style.background = '#FFFFFF';
		ob.hideContainer.style.border = '1px #CCCCCC dashed';
		ob.hideContainer.style.width = '250px';
		ob.hideContainer.style.color = '#AAAAAA';
		ob.hideContainer.style.padding = '13px 0px 3px 0px';
		ob.hideContainer.style.cursor = 'pointer';
		ob.hideContainer.innerHTML = BPMESS['PARA_MIN'];
		ob.hideContainer.onclick = ob.OnHideClick;

		ob.childsContainer = ob.container.appendChild(_crt(1, 3));
		ob.childsContainer.rows[0].cells[0].width = '15%';
		ob.childsContainer.rows[0].cells[1].width = '70%';
		ob.childsContainer.rows[0].cells[2].width = '15%';
		ob.childsContainer.rows[0].cells[1].style.border = '2px #dfdfdf dashed';
		ob.childsContainer.id = ob.Name;

		if (ob.Properties['_DesMinimized'] == 'Y')
		{
			ob.childsContainer.style.display = 'none';
		}
		else
		{
			ob.hideContainer.style.display = 'none';
		}

		ob.childsContainer.rows[0].cells[1].style.padding = '10px';

		ob.childActivities[0].Draw(ob.childsContainer.rows[0].cells[1]);
	};


	ob.CheckFields = function ()
	{
		return true;
	};

	ob.OnHideClick = function ()
	{
		if(ob.Properties['_DesMinimized'] == 'Y')
		{
			ob.Properties['_DesMinimized'] = 'N';
			ob.childsContainer.style.display = 'table';
			ob.hideContainer.style.display = 'none';
		}
		else
		{
			ob.Properties['_DesMinimized'] = 'Y';
			ob.childsContainer.style.display = 'none';
			ob.hideContainer.style.display = 'block';
		}
	};

	return ob;
};
