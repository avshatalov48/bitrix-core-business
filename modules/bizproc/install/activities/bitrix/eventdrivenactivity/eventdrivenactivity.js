/////////////////////////////////////////////////////////////////////////////////////
// EventDrivenActivity
/////////////////////////////////////////////////////////////////////////////////////
EventDrivenActivity = function()
{
	var ob = new SequentialWorkflowActivity();
	ob.Type = 'EventDrivenActivity';

	ob.DrawSequentialWorkflowActivity = ob.Draw;
	ob.Draw = function (d)
	{
		if(ob.parentActivity.Type == 'StateActivity')
			ob.DrawSequentialWorkflowActivity(d);
		else
			ob.DrawSequenceActivity(d);
	};

	ob.AfterSDraw = function ()
	{
		if(ob.parentActivity.Type == 'StateActivity' && ob.childsContainer.rows.length>2)
		{
			ob.childsContainer.rows[0].style.display = 'none';
			ob.childsContainer.rows[1].style.display = 'none';
		}
	};

	ob.SetError = function (s, setFocus)
	{
		ob.parentActivity.SetError(s, setFocus);
	};

	return ob;
};